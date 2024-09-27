<?php

require_once __DIR__ . '/../../../../globals.php';

use OpenEMR\Core\Header;
use OpenEMR\Module\CustomModuleCdss\Bootstrap;
use OpenEMR\Modules\CustomModuleCdss\GlobalConfig;

$bootstrap = new Bootstrap($GLOBALS['kernel']->getEventDispatcher());
$globalsConfig = $bootstrap->getGlobalConfig();
$showPlandefinition = $bootstrap->getGlobalConfig()->getGlobalSetting(GlobalConfig::CONFIG_SHOW_PLANDEFINITION_URL);
require_once "../src/CdssFHIRAPIController.php";

$planDefinitionIds = $globalsConfig->getPlanDefinitionIds();

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
                                                <div class="row justify-content-center">
                                                    <div class="col-md-8 d-flex align-items-center">
                                                        <div class="form-group mb-0 mr-2 flex-grow-1">
                                                            <select class="form-control" id="planDefinitionSelect">
                                                                <option value="" disabled selected>Please select a plan definition</option>
                                                                <?php if(count($planDefinitionIds)==1): ?>
                                                                    <option value="<?php echo($planDefinitionIds[0]) ?>" selected><?php echo($planDefinitionIds[0]) ?></option>
                                                                <?php else: ?>
                                                                <?php foreach($planDefinitionIds as $planId): ?>
                                                                    <option value="<?php echo($planId) ?>"><?php echo($planId) ?></option>
                                                                <?php endforeach ; endif ;?>
                                                            </select>
                                                        </div>
                                                        <button class="btn btn-primary" onclick="planDefinitionData()">Execute</button>
                                                    </div>
                                                    
                                                </div>
                                                <div class="d-flex justify-content-center mt-4">
                                                    <div id="spinner-plan" style="display: none;" class="spinner-border spinner-border-slow" style="animation: spinner-border 1.5s linear infinite;" role="status">
                                                    </div>
                                                </div>
                                                
                                                <div id="content-plan-definition" style="<?php echo count($planDefinitionIds)>1 ? 'display: none;' : '' ?>"> 
                                                <?php if($showPlandefinition){ ?>
                                                <div class="mt-4 col-12" id="url-show">
                                                    <form action="CdssModule.php" method="$_GET">
                                                        <div class="input-group mb-3">
                                                            <!-- <span class=" input-group-text bg-info" id="url-plan-definition">GET</span> -->
                                                            <input placeholder="Base url" id="url-plan-definition" disabled 
                                                            type="text" class="form-control w-full" name="url-plan-definition" id="url-plan" value="" id="basic-url" aria-describedby="patient-1">
                                                        </div>
                                                    </form>
                                                </div>
                                                <?php } ?>
                                                <div class="row mb-4 mt-4">
                                                    <div class="col">
                                                        <h2 class="text-center" id="titlePLan">  </h2>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <p class="lead" id="description-plan">  </p>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <h4>Documentation</h4>
                                                        <ul id="ul-documentation">
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <h4>Results</h4>
                                                    </div>
                                                </div>
                                                <div class="col-12" id="div-results">
                                                </div>
                                                <div class="row mt-5" id="errorsPlanDefinition">
                                                    <div class="col-12">
                                                        <p>
                                                            <a class="btn btn-link" data-toggle="collapse" href="#errorWarnings" role="button" aria-expanded="false" aria-controls="errorWarnings">
                                                                There were errors while executing the Plan Definition (click to show)
                                                            </a>
                                                        </p>
                                                        <div class="collapse" id="errorWarnings">
                                                            <div class="card card-body">
                                                                <ul id="ul_errors_list">
                                                                </ul>
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
        </div>
    </div>
</body>
</html>
<script>

    function planDefinitionData(){
        $('#spinner-plan').show();
        $('#url-show').hide()
        $('#content-plan-definition').hide();
        let id = $('#planDefinitionSelect').val();
        let pid = <?php echo($pid); ?> ;
        $.ajax({
            url: '../src/ajaxPlandefinition.php',
            method: 'POST',
            data: { planDefinitionId:id, pid:pid},
            success: function(response) {
                $('#spinner-plan').hide();
                
                var jsonResponse = JSON.parse(response); 
                $('#titlePLan').text(jsonResponse.planDefinitionGet.action[0].title);
                $('#url-plan-definition').val(jsonResponse.url);
                $('#description-plan').text(jsonResponse.planDefinitionGet.action[0].description);
                let documentationData  = jsonResponse.planDefinitionGet.action[0].documentation ;
                let planDefinitionErrors = jsonResponse.planDefinitionProblems ? jsonResponse.planDefinitionProblems : null;
                $('#ul-documentation').empty();
                $('#ul_errors_list').empty();

                if(planDefinitionErrors){
                    $("#errorsPlanDefinition").show();
                    $.each(planDefinitionErrors, function(index,value){
                        let lastItem = $('<li></li>');
                        let message = value;
                        lastItem.append(message);
                        $('#ul_errors_list').append(lastItem);
                        planDefinitionErrors = null;
                    });
                }
                
                $.each(documentationData, function(index, documentation) {
                    var listItem = $('<li></li>');
                    var link = $('<a></a>').attr('href', documentation.url).attr('target', '_blank').text(documentation.display);
                    listItem.append(link);
                    $('#ul-documentation').append(listItem);
                });

                var resultsDiv = $("#div-results"); 
                resultsDiv.empty();
                var aux = true;
                var planDefinitionData = jsonResponse.planDefinition;

                if (planDefinitionData && planDefinitionData.entry && Array.isArray(planDefinitionData.entry)) {

                    $.each(planDefinitionData.entry, function(index, entry) {
                        if (entry.resource && entry.resource.action) {
                            $.each(entry.resource.action, function(i, action) {
                                if (action.title && action.description) {
                                    var cardHtml = '<div class="card mb-3">';
                                    cardHtml += '<div class="card-header"><h5>' + action.title + '</h5></div>';
                                    cardHtml += '<div class="card-body"><p class="card-text">' + action.description + '</p></div>';
                                    cardHtml += '</div>';
                                    resultsDiv.append(cardHtml);
                                    aux = false;
                                }
                            });
                        }
                    });

                    if (aux) {
                        let cardHtml = '<div class="card mb-3">';
                        cardHtml += '<div class="card-header"><h5>No recommendations</h5></div>';
                        cardHtml += '<div class="card-body"><p class="card-text">There are no recommendations from this clinical practice guideline for this patient.</p></div>';
                        cardHtml += '</div>';
                        resultsDiv.append(cardHtml);
                    }
                } else {
                    resultsDiv.append('<div class="alert alert-danger" role="alert">There was an error executing the rules on the FHIR server.</div>');
                }
                $('#url-show').show();

                $('#content-plan-definition').show();
                
            },
            error: function() {
                $('#spinner-plan').hide();
                $('#content-plan-definition').hide();
                alert('Error executing the plan definition.');
            }
        });
    }


    $(document).ready(function(){
        $("#errorsPlanDefinition").hide();
        <?php if(count($planDefinitionIds)==1): ?>
            planDefinitionData();
        <?php endif ?>
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


