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
$bootstrap = new Bootstrap($GLOBALS['kernel']->getEventDispatcher());
$globalsConfig = $bootstrap->getGlobalConfig();
require_once "../src/CdssFHIRAPIController.php";



$url = $globalsConfig->getTextOption(); 
$idPlanDefinition = $globalsConfig->getIdPlanDefinitionOption();
$sql = "select uuid from patient_data where id = ?";
$uuid = sqlQuery($sql, array($pid));

$planDefinition = false;

if($uuid && $url && $idPlanDefinition){
    $string_uuid = UuidRegistry::uuidToString($uuid['uuid']);
    $patientData = ['uuid_string' => $string_uuid,'url' => $url];
    $CdssFHIRApiController = new CdssFHIRAPIController();
    $response = $CdssFHIRApiController->createOrUpdatePatientResource($patientData);

    if($response){
        $responseObject = json_decode($response);
        $immunizationData = ['uuid_string' => $string_uuid, 'uuid_string_replace' => $responseObject->id,'url' => $url];
        $immunizationResource = $CdssFHIRApiController->createOrUpdateImmunizationResource($immunizationData);

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
                                <li class="nav-item" id='li-create-p'>
                                    <a href='#' class="active nav-link font-weight-bold" id='patient-create'><?php echo xlt('Get Plan Definition'); ?></a>
                                </li>
                                
                            </ul>
                            <div class="col-sm-12 col-md-12 col-lg-12">
                                <div class="mt-2 col-12">
                                    <form action="CdssModule.php" method="$_GET">
                                        <div class="input-group mb-3">
                                            <span class=" input-group-text bg-info" id="url-plan-definition">GET</span>
                                            <input placeholder="Base url" id="url-plan-definition" disabled 
                                            type="text" class="form-control w-full" name="url-plan-definition" value="<?php echo $url."/fhir/PlanDefinition/".$idPlanDefinition."/$apply?subject=Patient/".$string_uuid; ?>" id="basic-url" aria-describedby="patient-1">
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="row" id="show_create_patient" style="display: none;">
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <div class="ml-3 mb-3">
                                            <label for="textAreaPlanDefinition" class="form-label">Response</label>
                                            <?php if($planDefinition && $data->message){ ?>
                                            <p class="form-control w-full"rows="3">
                                                <?php echo ($data->message) ?>
                                            </p>
                                            <?php } elseif($planDefinition){ ?>
                                            <textarea class="form-control w-full" style="" id="textAreaPlanDefinition" rows="3" disabled>
                                                <?php echo ($planDefinition) ?>
                                            </textarea>
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
    $("#patient-li").click(function(){
        $("#patient-div").show(450);
        $("#immunization-div").hide(450);
        $("#plan-definition-div").hide(450);
        $("#immunization-li").removeClass("active");
        $("#patient-li").addClass("active");
        $("#plan-definition-li").removeClass("active");    
    });
    $("#immunization-li").click(function (){
        $("#patient-div").hide(450);
        $("#immunization-div").show(450);
        $("#plan-definition-div").hide(450);
        $("#immunization-li").addClass("active");
        $("#patient-li").removeClass("active");
        $("#plan-definition-li").removeClass("active");        
    });
    $("#plan-definition-li").click(function (){
        $("#patient-div").hide(450);
        $("#immunization-div").hide(450);
        $("#plan-definition-div").show(450);
        $("#immunization-li").removeClass("active");
        $("#patient-li").removeClass("active");
        $("#plan-definition-li").addClass("active");        
    });

    $("#li-create-p").click(function(){
        $("#patient-update").removeClass("active");
        $("#patient-create").addClass("active");
    });

    $("#li-update-p").click(function(){
        $("#patient-create").removeClass("active");
        $("#patient-update").addClass("active");
    })


    })
</script>


