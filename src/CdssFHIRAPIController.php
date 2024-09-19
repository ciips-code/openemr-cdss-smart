<?php

namespace OpenEMR\Module\CustomModuleCdss;


// use HttpRequest;

use Exception;
use OpenEMR\Module\CustomModuleCdss\CdssFHIRPatientResource;
use Symfony\Component\HttpFoundation\JsonResponse;
include_once '../src/CdssFHIRPatientResource.php';
include_once '../src/CdssFHIRImmunizationResource.php';
include_once '../src/CdssFHIRConditionResource.php';    
include_once '../src/CdssCommunicationService.php';


class CdssFHIRAPIController{

    public function createOrUpdatePatientResource(array $data){

        if($data['uuid_string'] == '' || !$data['uuid_string']){
            return new JsonResponse([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property uuid does not exist.',
            ]);
        }
        if($data['url'] == '' || !$data['url']){
            return new JsonResponse([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property url does not exist.',
            ]);
        }
        $patienResource = new CdssFHIRPatientResource();

        $patienResource = $patienResource->getOne($data['uuid_string']);
        try{
            $communicationService = new CdsssCommunicationService($patienResource,$data['url'].'/fhir/Patient/'.$data['uuid_string'],'PUT');
            $url= $data['url'].'/fhir/Patient/'.$data['uuid_string'];
            $response = $communicationService->sendRequest();
            $sql = "INSERT INTO openemr.ciips_cdss_log(datetime, method, url, data, response) VALUES(?, ?, ?, ?, ?)";
            sqlStatement($sql,array(date("Y-m-d H:i:s"),'PUT',$url,json_encode($patienResource), json_encode($response)));
            return $response;

        }catch(Exception $e){
            return new JsonResponse([
                'status' => false,
                'code' => $e->getCode(),
                'message' => 'Error, property url does not exist.'.$e->getMessage(),
            ]);
        }

    }
    public function createOrUpdateImmunizationResource(array $data){
        if($data['uuid_string'] == '' || $data['uuid_string_replace'] == ''){
            return new JsonResponse([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property uuid does not exist.',
            ]);
        }
        if($data['url'] == '' || !$data['url']){
            return new JsonResponse([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property url does not exist.',
            ]);
        }
        $immunizationResource = new CdssFHIRImmunizationResource();
        $allImmunizationResource = $immunizationResource->getAll('',$data['uuid_string']);
        if($allImmunizationResource){
            $resource = json_decode($allImmunizationResource,true);
            foreach($resource['entry'] as &$entry){
                $uuid = $entry['resource']['id'];
                $immunizationResourceModify = $immunizationResource->getOne($uuid);
                if($immunizationResourceModify){
                    $parseImmunization = $immunizationResource->parseResourcePatientId($data['uuid_string_replace'],$immunizationResourceModify);
                    try{
                        $communicationService = new CdsssCommunicationService($parseImmunization,$data['url'].'/fhir/Immunization','POST');
                        $communicationService->sendRequest();
                    }catch(Exception $e){
                        
                    }
                }
            }
        }
    }

    public function createProcedureResource(array $data){
        if($data['uuid_string'] == ''){
            return new JsonResponse([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property uuid does not exist.',
            ]);
        }
        $procedureResource = new CdssFHIRProcedureResource();
        $procedureResource->verifyProcedureOrderReport($data['id']);
        $allProcedureResource = $procedureResource->getAll($data['uuid_string']);
        if($allProcedureResource){
            $resource = json_decode($allProcedureResource,true);
            foreach($resource['entry'] as &$entry){
                $uuid = $entry['resource']['id'];
                $procedureResourceModify = $procedureResource->getOne($uuid);
                if($procedureResourceModify){
                    $parseResource = $procedureResource->parseResourceEncounter($procedureResourceModify);
                    $resourceArray = json_decode($parseResource,true);
                    try{

                        $url = $data['url'].'/fhir/Procedure/'.$resourceArray['id'];
                        $communicationService = new CdsssCommunicationService($parseResource,$url,'PUT');
                        $response = $communicationService->sendRequest();
                        $sql = "INSERT INTO openemr.ciips_cdss_log(datetime, method, url, data, response) VALUES(?, ?, ?, ?, ?)";
                        sqlStatement($sql,array(date("Y-m-d H:i:s"),'PUT',$url,$parseResource, json_encode($response)));

                    }catch(Exception $e){
                        
                    }
                }
            }
        }
    }

    public function applyPatientPlanDefinition(array $data){
        if($data['uuid_string'] == '' || !$data['uuid_string']){
            return json_encode([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property uuid does not exist.',
            ]);
        }
        if($data['plan_definition_id'] == '' || !$data['plan_definition_id']){
            return new JsonResponse([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property plan_definition_id does not exist.',
            ]);
        }
        if($data['url'] == '' || !$data['url']){
            return new JsonResponse([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property url does not exist.',
            ]);
        }
        try{  
            $url= $data['url'].'/fhir/PlanDefinition/'.$data['plan_definition_id'].'/$r5.apply?subject=Patient/'.$data['uuid_string'];
            if($data['GET']){
                $url= $data['url'].'/fhir/PlanDefinition/'.$data['plan_definition_id'];
            }
            $communicationService = new CdsssCommunicationService(null,$url,'GET');
            $response = $communicationService->sendRequest();
            $sql = "INSERT INTO openemr.ciips_cdss_log(datetime, method, url, data, response) VALUES(?, ?, ?, ?, ?)";
            sqlStatement($sql,array(date("Y-m-d H:i:s"),'GET',$url,NULL, json_encode($response)));
            return $response;
        }catch(Exception $e){
            return new JsonResponse([
                'status' => false,
                'code' => $e->getCode(),
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function createOrUpdateConditionResource(array $data){
        if($data['uuid_string'] == ''){
            return new JsonResponse([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property uuid does not exist.',
            ]);
        }
        if($data['url'] == '' || !$data['url']){
            return new JsonResponse([
                'status' => false,
                'code' => 400,
                'message' => 'Error, property url does not exist.',
            ]);
        }
        $conditionResource = new CdssFHIRConditionResource();
        $allConditionResource = $conditionResource->getAll('',$data['uuid_string']);

        if ($allConditionResource) {
            $resource = json_decode($allConditionResource,true);

            foreach ($resource['entry']  as $entry) {
                if (isset($entry['resource']['code']['coding'][0]['code']) && $entry['resource']['code']['coding'][0]['code'] === '2F90.0') {
                    $entry['resource']['code']['coding'][0] = [
                        'code' => '269533000',
                        'display' => 'Carcinoma of colon (disorder)',
                        'system' => 'http://snomed.info/sct'
                    ];
        
                    $conditionJson = json_encode($entry['resource']);
        
                    $url = $data['url'] . '/fhir/Condition/' . $entry['resource']['id'];
                    $communicationService = new CdsssCommunicationService($conditionJson, $url, 'PUT');
                    $response = $communicationService->sendRequest();
        
                    $sql = "INSERT INTO openemr.ciips_cdss_log (`datetime`, `method`, `url`, `data`, `response`) VALUES (?, ?, ?, ?, ?)";
                    sqlStatement($sql, array(date("Y-m-d H:i:s"),'PUT', $url, $conditionJson, json_encode($response)));
                }
            }
        } 

    }
}