<?php

require_once __DIR__ . '/../../../../globals.php';


use Symfony\Component\HttpClient\HttpClient;
use OpenEMR\Core\Header;
use OpenEMR\RestControllers\PatientRestController;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\RestControllers\FHIR\FhirPatientRestController;
use OpenEMR\RestControllers\RestControllerHelper;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Module\CustomModuleCdss\Bootstrap;
use OpenEMR\Module\CustomModuleCdss\CdssFHIRAPIController;
use OpenEMR\Modules\CustomModuleCdss\GlobalConfig;

$bootstrap = new Bootstrap($GLOBALS['kernel']->getEventDispatcher());
$globalsConfig = $bootstrap->getGlobalConfig();
$showPlandefinition = $bootstrap->getGlobalConfig()->getGlobalSetting(GlobalConfig::CONFIG_SHOW_PLANDEFINITION_URL);
require_once "../src/CdssFHIRAPIController.php";



$url = $globalsConfig->getTextOption(); 
$idPlanDefinition = $globalsConfig->getIdPlanDefinitionOption();
$sql = "select uuid from patient_data where id = ?";
$uuid = sqlQuery($sql, array($pid));
$planDefinition = false;
$uuidServerResponse = null;
if($uuid && $url && $idPlanDefinition){
    $string_uuid = UuidRegistry::uuidToString($uuid['uuid']);
    $patientData = ['uuid_string' => $string_uuid,'url' => $url];
    $CdssFHIRApiController = new CdssFHIRAPIController();
    $response = $CdssFHIRApiController->createOrUpdatePatientResource($patientData);    

    if($response){
        $responseObject = json_decode($response);
        $uuidServerResponse = $responseObject->id;
        $conditionData = ['uuid_string' => $string_uuid,'url' => $url];
        $conditionResource = $CdssFHIRApiController->createOrUpdateConditionResource($conditionData);

        $planDefinitionData = ['uuid_string' => $responseObject->id,'url' => $url,'plan_definition_id' => $idPlanDefinition];
        $planDefinition = $CdssFHIRApiController->applyPatientPlanDefinition($planDefinitionData);
        $data = json_decode($planDefinition);
    }
    }else{
        $planDefinition = false;
    }
 ?>

<script>
    function restoreSession() {
    return opener.top.restoreSession();
}
</script>
<html>
<head>
    <script src="../../../../../public/assets/jquery/dist/jquery.js"></script>

<?php Header::setupHeader(); ?>

<title>Cdss</title>

</head>
<style>
        textarea {
            width: 100%; /* Ajusta el ancho del textarea según tus necesidades */
            box-sizing: border-box; /* Asegura que el padding se incluya en el ancho */
            overflow: hidden; /* Evita barras de desplazamiento */
        }
</style>
<body>
    <div class="container mt-3">
    <div class="row oe-margin-b-10">
                <div class="col-12">
                
                </div>

            </div>
            <div>

                <h2 class="text-left"><a name='entire_doc'><?php echo xlt("Cdss Module");?></a></h2>
                
            </div>
        
            <div class="row" id="patient-div">
                <div class="col-sm-12">
                    <div class="jumbotron jumbotron-fluid py-3">
                        <div class="col-sm-12 col-md-12 col-lg-12">
                            
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a href="../../../../patient_file/summary/demographics.php" class="btn btn-info btn-back nav-link" onclick="top.restoreSession()"><?php echo xlt('Back to Patient'); ?></a>
                                </li>
                            </ul>
                            <div class="col-sm-12 col-md-12 col-lg-12">
                                <?php if($showPlandefinition){ ?>
                                    <div class="mt-2 col-12">
                                        <form action="CdssModule.php" method="$_GET">
                                            <div class="input-group mb-3">
                                                <span class=" input-group-text bg-info" id="url-plan-definition">GET</span>
                                                <input placeholder="Base url" id="url-plan-definition" disabled 
                                                type="text" class="form-control w-full" name="url-plan-definition" value="<?php echo $url."/fhir/PlanDefinition/".$idPlanDefinition.'/$r5.apply?subject=Patient/'.($uuidServerResponse ?? ''); ?>" id="basic-url" aria-describedby="patient-1">
                                            </div>
                                        </form>
                                    </div>
                                <?php } ?>
                                <div class="row" id="show_create_patient" style="display: none;">
                                    <div class="col-sm-12 col-md-12 col-lg-12 mt-4">
                                        <div class="ml-3 mb-3">
                                            <?php if($planDefinition && $data->message){ ?>
                                            <p class="form-control w-full"rows="3">
                                                <?php echo ($data->message) ?>
                                            </p>
                                            <?php } elseif($planDefinition){ 
                                                        $planDefinitionData = json_decode($planDefinition, true); 
                                                        $aux=true;
                                                        if (isset($planDefinitionData['entry']) && is_array($planDefinitionData['entry'])) {
                                                            foreach ($planDefinitionData['entry'] as $entry) {
                                                                if (isset($entry['resource']['action']) && is_array($entry['resource']['action'])) {
                                                                    foreach ($entry['resource']['action'] as $action) {
                                                                        if (isset($action['title']) && isset($action['description'])) {
                                                                            echo "<h2>" . htmlspecialchars($action['title']) . "</h2>";
                                                                            echo "<p>" . htmlspecialchars($action['description']) . "</p>";
                                                                            $aux=false;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            if($aux){
                                                                echo "<p>No se encontraron recomendaciones.</p>";
                                                            }
                                                        } else {
                                                            echo "<p>Error con el servidor fhir.</p>";
                                                        } ?>
                                            <?php } ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</body>
</html>
<script>
    $(document).ready(function(){
        <?php //if($_REQUEST['add-url-patient']){ ?>
            $("#show_create_patient").fadeIn(450);
        <?php //} ?>

        const $textarea = $('#textAreaPlanDefinition');

        function autoResize() {
            $textarea.css('height', 'auto');
            $textarea.css('height', $textarea[0].scrollHeight + 'px');
        }

        $textarea.on('input', autoResize);
        
        autoResize();

    })
</script>


