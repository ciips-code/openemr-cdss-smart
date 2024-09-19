<?php
namespace OpenEMR\Module\CustomModuleCdss;

use OpenEMR\RestControllers\FHIR\FhirProcedureRestController;

class CdssFHIRProcedureResource {

    public function getOne($string_uuid){
        $procedures = (new FhirProcedureRestController())->getOne($string_uuid);
        return json_encode($procedures);
    }
    public function getAll($params,$uuidString =null){
        $procedures = (new FhirProcedureRestController())->getAll(['patient' => $params]);
        return json_encode($procedures);
    }

    public function verifyProcedureOrderReport ($pId){
        $sql = "SELECT po.procedure_order_id,po.date_ordered FROM openemr.procedure_order po LEFT JOIN openemr.procedure_report pr ON po.procedure_order_id = pr.procedure_order_id 
        WHERE pr.procedure_report_id IS NULL 
        AND po.patient_id = ?";
        $response = sqlStatement($sql,array($pId));

        while($row = SqlFetchArray($response)){
            $sql = "INSERT INTO openemr.procedure_report (procedure_order_id,date_collected,date_report) VALUES (?,?,?)";
            $response = sqlStatement($sql,array($row['procedure_order_id'],$row['date_ordered'],$row['date_ordered'])); 
        }
    }

    public function parseResourceEncounter($procedure){
        $parseProcedure = json_decode($procedure,true);
        if($parseProcedure['encounter']){
            unset($parseProcedure['encounter']);
        }
        return json_encode($parseProcedure);
    }
}