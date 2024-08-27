<?php


namespace OpenEMR\Module\CustomModuleCdss;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Core\TwigEnvironmentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OpenEMR\Events\Patient\Summary\Card\RenderEvent as CardRenderEvent;

use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Modules\CustomModuleCdss\GlobalConfig;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\PatientDemographics\RenderEvent;

require "GlobalConfig.php";

class Bootstrap {
    const MODULE_INSTALLATION_PATH = "/interface/modules/custom_modules/";
    const MODULE_NAME = "custom-module-cdss";

    /**
     * @var EventDispatcherInterface The object responsible for sending and subscribing to events through the OpenEMR system
     */
    private $eventDispatcher;

    /**
     * @var GlobalConfig Holds our module global configuration values that can be used throughout the module.
     */
    private $globalsConfig;

    /**
     * @var string The folder name of the module.  Set dynamically from searching the filesystem.
     */
    private $moduleDirectoryName;

    /**
     * @var \Twig\Environment The twig rendering environment
     */
    private $twig;

    /**
     * @var SystemLogger
     */
    private $logger;

    public function __construct(EventDispatcherInterface $eventDispatcher, ?Kernel $kernel = null)
    {
        global $GLOBALS;

        if (empty($kernel)) {
            $kernel = new Kernel();
        }

        // NOTE: eventually you will be able to pull the twig container directly from the kernel instead of instantiating
        // it here.
        $twig = new TwigContainer($this->getTemplatePath(), $kernel);
        $twigEnv = $twig->getTwig();
        $this->twig = $twigEnv;

        $this->moduleDirectoryName = basename(dirname(__DIR__));
        $this->eventDispatcher = $eventDispatcher;

        // we inject our globals value.
        $this->globalsConfig = new GlobalConfig($GLOBALS);
        $this->logger = new SystemLogger();
    }

    public function getGlobalConfig(){
        return $this->globalsConfig;
    }

    public function subscribeToEvents(){
        $this->addGlobalSettings();

        if ($this->globalsConfig->isConfigured()) {
            // $this->registerMenuItems();
            $this->registerTemplateEvents();
        };
    }

    public function addGlobalSettings(){
        $this->eventDispatcher->addListener(GlobalsInitializedEvent::EVENT_HANDLE, [$this, 'addGlobalSettingsSection']);
    }

    public function addGlobalSettingsSection(GlobalsInitializedEvent $event){

        global $GLOBALS;

        $service = $event->getGlobalsService();
        $section = xlt("Custom Module CDSS");
        $service->createSection($section, 'Appearence');

        $settings = $this->globalsConfig->getGlobalSettingSectionConfiguration();

        foreach ($settings as $key => $config) {
            $value = $GLOBALS[$key] ?? $config['default'];
            $service->appendToSection(
                $section,
                $key,
                new GlobalSetting(
                    xlt($config['title']),
                    $config['type'],
                    $value,
                    xlt($config['description']),
                    true
                )
            );
        }
    }

    public function registerTemplateEvents()
    {
        if ($this->getGlobalConfig()->getGlobalSetting(GlobalConfig::CONFIG_ENABLE_BUTTON_CMODULE)) {
            
                $this->eventDispatcher->addListener(RenderEvent::EVENT_SECTION_LIST_RENDER_AFTER, [$this, 'addTemplateOverrideLoader']);
            
        }
    }

    public function addTemplateOverrideLoader(){
        
        $twig = $this->twig;
        $ed = $GLOBALS['kernel']->getEventDispatcher();

        $id = "cdss_link";
        $dispatchResult = $ed->dispatch(CardRenderEvent::EVENT_HANDLE, new CardRenderEvent('cdssEvent'));
        echo $twig->render('dashboard/cdss-module-button.html.twig',['id' => $id,'directory'=>$this->getPublicPath().'CdssModule.php']);

    }
    private function getPublicPath()
    {
        return self::MODULE_INSTALLATION_PATH . ($this->moduleDirectoryName ?? '') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR;
    }

    private function getAssetPath()
    {
        return $this->getPublicPath() . 'assets' . DIRECTORY_SEPARATOR;
    }

    public function getTemplatePath()
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }
}
