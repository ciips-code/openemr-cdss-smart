<?php

namespace OpenEMR\Module\CustomModuleCdss;


// use HttpRequest;

use Exception;
use OpenEMR\Module\CustomModuleCdss\CdssFHIRPatientResource;
use Symfony\Component\HttpFoundation\JsonResponse;
include_once '../src/CdssFHIRPatientResource.php';
include_once '../src/CdssFHIRImmunizationResource.php';
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
            sqlStatement($sql,array('2020-08-02','PUT',$url,json_encode($patienResource), json_encode($response)));
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
            $communicationService = new CdsssCommunicationService(null,$data['url'].
            '/fhir/PlanDefinition/'.$data['plan_definition_id'].'/$r5.apply?subject=Patient/'.$data['uuid_string'],'GET');
            $response = $communicationService->sendRequest();
            $url= $data['url'].'/fhir/PlanDefinition/'.$data['plan_definition_id'].'/$r5.apply?subject=Patient/'.$data['uuid_string'];
            $sql = "INSERT INTO openemr.ciips_cdss_log(datetime, method, url, data, response) VALUES(?, ?, ?, ?, ?)";
            sqlStatement($sql,array('2020-08-02','GET',$url,NULL, json_encode($response)));
            return $response;
        }catch(Exception $e){
            return new JsonResponse([
                'status' => false,
                'code' => $e->getCode(),
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }
    }
}