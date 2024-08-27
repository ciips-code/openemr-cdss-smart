<?php 

namespace OpenEMR\Module\CustomModuleCdss;

use OpenEMR\RestControllers\FHIR\FhirImmunizationRestController;

class CdssFHIRImmunizationResource {

    public function getAll ($searchParams,$pstring_uuid) {
        $patient_fhir_data = (new FhirImmunizationRestController())->getAll('',$pstring_uuid);
        return json_encode($patient_fhir_data);
    }

    public function getOne ($string_uuid) {
        $patient_fhir_data = (new FhirImmunizationRestController())->getOne($string_uuid);
        return json_encode($patient_fhir_data);
    }
    
    public function parseResourcePatientId($uuid_string,$resourceData){

        $data = json_decode($resourceData);
        
        $uuid = $data->patient->reference;
        if($uuid){
            $newuuid = str_replace("Patient/",'',$uuid);
            $data->patient->reference = "Patient/".$uuid_string;
        }
        return json_encode($data);
    }
}