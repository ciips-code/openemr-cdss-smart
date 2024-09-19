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
use OpenEMR\Module\CustomModuleCdss\CdssFHIRPatientResource;
use OpenEMR\Module\CustomModuleCdss\CdssFHIRProcedureResource;

use OpenEMR\Modules\CustomModuleCdss\GlobalConfig;

$bootstrap = new Bootstrap($GLOBALS['kernel']->getEventDispatcher());
$globalsConfig = $bootstrap->getGlobalConfig();
$showPlandefinition = $bootstrap->getGlobalConfig()->getGlobalSetting(GlobalConfig::CONFIG_SHOW_PLANDEFINITION_URL);
require_once "../src/CdssFHIRAPIController.php";
require_once "../src/CdssFHIRProcedureResource.php";


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
    // var_dump($response);
    if($response){
        $responseObject = json_decode($response);
        $uuidServerResponse = $responseObject->id;
        $createOrUpdateProcedureOrder = $CdssFHIRApiController->createProcedureResource(['uuid_string' => $string_uuid,'url'=>$url]);
        $conditionData = ['uuid_string' => $string_uuid,'url' => $url];
        $conditionResource = $CdssFHIRApiController->createOrUpdateConditionResource($conditionData);
        $planDefinitionData = ['uuid_string' => $responseObject->id,'url' => $url,'plan_definition_id' => $idPlanDefinition];
        $planDefinition = $CdssFHIRApiController->applyPatientPlanDefinition($planDefinitionData);
        $planDefinitionDataGet = ['uuid_string' => $responseObject->id,'url' => $url,'plan_definition_id' => $idPlanDefinition,'GET'=>true];
        $planDefinitionGet = $CdssFHIRApiController->applyPatientPlanDefinition($planDefinitionDataGet);
        $data = json_decode($planDefinition);
        $dataGet = json_decode($planDefinitionGet);
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

<title>SMART CDSS</title>

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
        <div class="row" id="patient-div">
            <div class="col-sm-12">
                <div class="jumbotron jumbotron-fluid py-3">
                    <div class="col-sm-12 col-md-12 col-lg-12">
                        
                        <div class="col-sm-12 col-md-12 col-lg-12">   
                            <div class="container ">
                                <div>
                                    <h1 class="text-left"><a name='entire_doc'>SMART CDSS</a></h1>
                                </div>
                                <div class="row" id="patient-div">
                                    <div class="col-sm-12">
                                        <div class="jumbotron jumbotron-fluid py-3">
                                            <div class="container">
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <a href="../../../../patient_file/summary/demographics.php" class="btn btn-primary btn-back"  onclick="top.restoreSession()">Back to Patient</a>
                                                    </div>
                                                </div>
                                                <?php if($showPlandefinition){ ?>
                                                <div class="mt-2 col-12">
                                                    <form action="CdssModule.php" method="$_GET">
                                                        <div class="input-group mb-3">
                                                            <!-- <span class=" input-group-text bg-info" id="url-plan-definition">GET</span> -->
                                                            <input placeholder="Base url" id="url-plan-definition" disabled 
                                                            type="text" class="form-control w-full" name="url-plan-definition" value="<?php echo $url."/fhir/PlanDefinition/".$idPlanDefinition.'/$r5.apply?subject=Patient/'.($uuidServerResponse ?? ''); ?>" id="basic-url" aria-describedby="patient-1">
                                                        </div>
                                                    </form>
                                                </div>
                                                <?php } ?>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <h2 class="text-center"> <?php echo($dataGet->action[0]->title); ?> </h2>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <p class="lead"><?php echo($dataGet->action[0]->description); ?> </p>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <h4>Documentation</h4>
                                                        <ul>
                                                            <?php foreach($dataGet->action[0]->documentation as $documentation){?>
                                                                <li><a href="<?php echo($documentation->url); ?>" target="_blank"><?php echo($documentation->display); ?></a></li>
                                                            <?php } ?>

                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <h4>Results</h4>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                <?php if($planDefinition && $data->message){ ?>
                                                    <p class="form-control w-full"rows="3">
                                                    <div class="row mb-4">
                                                        <div class="card mb-3">
                                                            <div class="card-header">
                                                                <h5><?php echo ($data->message) ?></h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    </p>                                                    
                                                    <div class="row mb-4">                                                            
                                                        <?php } elseif($planDefinition){ 
                                                            $planDefinitionData = json_decode($planDefinition, true); 
                                                            $aux=true;
                                                            if (isset($planDefinitionData['entry']) && is_array($planDefinitionData['entry'])) {
                                                                foreach ($planDefinitionData['entry'] as $entry) {
                                                                    if (isset($entry['resource']['action']) && is_array($entry['resource']['action'])) {
                                                                        foreach ($entry['resource']['action'] as $action) {
                                                                            if (isset($action['title']) && isset($action['description'])) {
                                                                                echo '<div class="card mb-3"><div class="card-header"><h5>' . htmlspecialchars($action['title']) . "</h5></div>";
                                                                                echo '<div class="card-body"><p class="card-text">' . htmlspecialchars($action['description']) . "</p></div></div>";
                                                                                $aux=false;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                if($aux){
                                                                    echo "<p>There are no recommendations from this clinical practice guideline for this patient.</p>";
                                                                }
                                                            } else {    
                                                                echo '<div class="alert alert-danger" role="alert">There was an error executing the rules on the FHIR server.</div>';
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


