<?php

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Module\CustomModuleCdss\Bootstrap;
use OpenEMR\Module\CustomModuleCdss\CdssFHIRAPIController;
use OpenEMR\Modules\CustomModuleCdss\GlobalConfig;

$bootstrap = new Bootstrap($GLOBALS['kernel']->getEventDispatcher());
$globalsConfig = $bootstrap->getGlobalConfig();
$showPlandefinition = $bootstrap->getGlobalConfig()->getGlobalSetting(GlobalConfig::CONFIG_SHOW_PLANDEFINITION_URL);
require_once "./CdssFHIRAPIController.php";



$url = $globalsConfig->getTextOption();
$idPlanDefinition = $_POST['planDefinitionId']; //$globalsConfig->getIdPlanDefinitionOption();
$pid=$_POST['pid'];
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
        $createOrUpdateProcedureOrder = $CdssFHIRApiController->createProcedureResource(['uuid_string' => $string_uuid,'url'=>$url,'id' => $pid]);

        $planDefinitionData = ['uuid_string' => $responseObject->id,'url' => $url,'plan_definition_id' => $idPlanDefinition];
        $planDefinition = $CdssFHIRApiController->applyPatientPlanDefinition($planDefinitionData);
        $planDefinitionDataGet = ['uuid_string' => $responseObject->id,'url' => $url,'plan_definition_id' => $idPlanDefinition,'GET'=>true];
        $planDefinitionGet = $CdssFHIRApiController->applyPatientPlanDefinition($planDefinitionDataGet);
        $data = json_decode($planDefinition);
        $dataGet = json_decode($planDefinitionGet);

        $urlReturn = $url."/fhir/PlanDefinition/".$idPlanDefinition.'/$r5.apply?subject=Patient/'.($uuidServerResponse ?? '');

        if($data || $dataGet ){
            http_response_code(200);
            echo json_encode(["planDefinition"=>$data,"planDefinitionGet"=>$dataGet,"url"=>$urlReturn]);
        }else{
            $planDefinition = false;
            http_response_code(400);
            echo json_encode(["planDefinition"=>$planDefinition]);
        }
    }
}else{
    $planDefinition = false;
    http_response_code(400);
    echo json_encode(["planDefinition"=>$planDefinition]);
}



