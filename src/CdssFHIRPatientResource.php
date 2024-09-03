<?php

namespace OpenEMR\Module\CustomModuleCdss;

use OpenEMR\RestControllers\FHIR\FhirPatientRestController;

class CdssFHIRPatientResource {

    public function getOne ($string_uuid) {
        $patient_fhir_data = (new FhirPatientRestController())->getOne($string_uuid);
        return json_encode($patient_fhir_data);
    }

}