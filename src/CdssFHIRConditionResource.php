<?php 

namespace OpenEMR\Module\CustomModuleCdss;

use OpenEMR\RestControllers\FHIR\FhirConditionRestController;

class CdssFHIRConditionResource {

    public function getAll ($searchParams,$pstring_uuid) {
        $condition_fhir_data = (new FhirConditionRestController())->getAll('',$pstring_uuid);
        return json_encode($condition_fhir_data);
    }

    public function getOne ($string_uuid) {
        $condition_fhir_data = (new FhirConditionRestController())->getOne($string_uuid);
        return json_encode($condition_fhir_data);
    }
    
}