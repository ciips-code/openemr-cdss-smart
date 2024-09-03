<?php

namespace OpenEMR\Modules\CustomModuleCdss;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Services\Globals\GlobalSetting;

class GlobalConfig{

    // const CONFIG_ENABLE_MENU = "cdss_add_menu_button";
    const CONFIG_ENABLE_FHIR_API = "cdss_enable_fhir_api";
    const CONFIG_ENABLE_BUTTON_CMODULE = "cdss_enable_button_cmodule";
    const CONFIG_URL_CMODULE = "cdss_url_cmodule";
    const CONFIG_PLANDEFINITION_ID_CMODULE = "cdss_pdid_cmodule";
    const CONFIG_SHOW_PLANDEFINITION_URL = "cdss_show_plandefinition_url";



    private $globalsArray;

    public function __construct(array $globalsArray)
    {
        $this->globalsArray = $globalsArray;
    }

    public function isConfigured()
    {
        $keys = [self::CONFIG_ENABLE_BUTTON_CMODULE,self::CONFIG_URL_CMODULE,self::CONFIG_PLANDEFINITION_ID_CMODULE];
        foreach ($keys as $key) {
            $value = $this->getGlobalSetting($key);
            if (empty($value)) {
                return false;
            }
        }
        return true;
    }

    public function getGlobalSetting($settingKey)
    {
        return $this->globalsArray[$settingKey] ?? null;
    }
    public function getTextOption()
    {
        return $this->getGlobalSetting(self::CONFIG_URL_CMODULE);
    }
    public function getIdPlanDefinitionOption()
    {
        return $this->getGlobalSetting(self::CONFIG_PLANDEFINITION_ID_CMODULE);
    }
    public function getGlobalSettingSectionConfiguration()
    {
        $settings = [
            self::CONFIG_ENABLE_BUTTON_CMODULE => [
                'title' => 'Enable button in patient dashboard'
                ,'description' => 'Shows a FHIR resource of a patient'
                ,'type' => GlobalSetting::DATA_TYPE_BOOL
                ,'default' => ''
            ],
            self::CONFIG_URL_CMODULE => [
                'title' => 'FHIR Server URL'
                ,'description' => 'API request url'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ],
            self::CONFIG_PLANDEFINITION_ID_CMODULE => [
                'title' => 'PlanDefinition ID'
                ,'description' => 'PlanDefinition ID'
                ,'type' => GlobalSetting::DATA_TYPE_TEXT
                ,'default' => ''
            ],
            self::CONFIG_SHOW_PLANDEFINITION_URL => [
                'title' => 'Show Plan Definition URL',
                'description' => 'Show or hide the PlanDefinition URL on the module screen',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => '' 
            ]
        ];
        return $settings;
    }
}