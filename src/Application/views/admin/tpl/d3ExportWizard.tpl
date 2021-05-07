[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{oxstyle include="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"}]
[{oxscript include="https://code.jquery.com/jquery-3.2.1.slim.min.js"}]
[{oxscript include="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"}]
[{oxscript include="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"}]
[{oxstyle include="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/solid.min.css"}]

<style>
    button {
        margin: 1em 1em 1em 5em;
    }
    html {
        font-size: 0.8em;
    }
    /* Image courtesy of gradientmagic.com */
    body {
        background-image: linear-gradient(339deg, rgba(47, 47, 47,0.02) 0%, rgba(47, 47, 47,0.02) 42%,transparent 42%, transparent 99%,rgba(17, 17, 17,0.02) 99%, rgba(17, 17, 17,0.02) 100%),linear-gradient(257deg, rgba(65, 65, 65,0.02) 0%, rgba(65, 65, 65,0.02) 11%,transparent 11%, transparent 92%,rgba(53, 53, 53,0.02) 92%, rgba(53, 53, 53,0.02) 100%),linear-gradient(191deg, rgba(5, 5, 5,0.02) 0%, rgba(5, 5, 5,0.02) 1%,transparent 1%, transparent 45%,rgba(19, 19, 19,0.02) 45%, rgba(19, 19, 19,0.02) 100%),linear-gradient(29deg, rgba(28, 28, 28,0.02) 0%, rgba(28, 28, 28,0.02) 33%,transparent 33%, transparent 40%,rgba(220, 220, 220,0.02) 40%, rgba(220, 220, 220,0.02) 100%),linear-gradient(90deg, rgb(255,255,255),rgb(255,255,255));
    }
    h4 .btn {
        font-size: 1.3rem;
    }
    h5.card-header {
        font-size: 1.1rem;
    }
</style>

[{capture name="d3script"}][{strip}]
    function startExport(id, format) {
        let elements = document.getElementsByClassName('errorbox');
        for (var i = 0; i < elements.length; i++){
            elements[i].style.display = 'none';
        }
        setTimeout(function(){
            document.getElementById('mask').className='';
            document.getElementById('popup2').className='d3loader-2';
        }, 3000);
        document.getElementById('mask').className='on';
        document.getElementById('popup2').className='d3loader-2 on';
        document.getElementById('exportid').value = id;
        document.getElementById('exportformat').value = format;
        document.getElementById('myedit').submit();
    }
[{/strip}][{/capture}]
[{oxscript add=$smarty.capture.d3script}]

<form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post" style="padding: 0;margin: 0;height:0;">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
    <input type="hidden" name="fnc" value="doExport">
    <input type="hidden" name="exportid" id="exportid" value="">
    <input type="hidden" name="exportformat" id="exportformat" value="CSV">

    [{assign var="groups" value=$oView->getGroups()}]
    [{if $groups|@count}]
        <div id="accordion">
            [{foreach from=$oView->getGroups() item="group"}]
                <div class="card mb-2">
                    <div class="card-header p-1" id="heading[{$group}]">
                        <h4 class="mb-0">
                            <span class="btn p-1" data-toggle="collapse" data-target="#collapse[{$group}]" aria-expanded="false" aria-controls="collapse[{$group}]">
                                [{oxmultilang ident=$group}]
                            </span>
                        </h4>
                    </div>

                    <div id="collapse[{$group}]" class="collapse" aria-labelledby="heading[{$group}]" data-parent="#accordion">
                        <div class="card-body pb-0">
                            <div class="row">
                                [{foreach from=$oView->getGroupExports($group) key="id" item="export"}]
                                    <div class="col-sm-6 col-md-4 col-lg-3 pb-4">
                                        <div class="card">
                                            <h5 class="card-header">
                                                [{$export->getTitle()}]
                                            </h5>
                                            <div class="card-body">
                                                <p class="card-text">
                                                    [{$export->getDescription()}]
                                                </p>

                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-primary" onclick="startExport('[{$id}]', 'CSV')">
                                                        <i class="fas fa-magic"></i>
                                                        [{oxmultilang ident=$export->getButtonText()}]
                                                    </button>
                                                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="sr-only">
                                                            <i class="fas fa-magic"></i>
                                                            [{oxmultilang ident=$export->getButtonText()}]
                                                        </span>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        [{block name="dataWizardExportFormat"}]
                                                            <button class="dropdown-item" onclick="startExport('[{$id}]', 'CSV')">
                                                                [{oxmultilang ident="D3_DATAWIZARD_EXPORT_FORMAT_CSV"}]
                                                            </button>
                                                            <button class="dropdown-item" onclick="startExport('[{$id}]', 'Pretty')">
                                                                [{oxmultilang ident="D3_DATAWIZARD_EXPORT_FORMAT_PRETTY"}]
                                                            </button>
                                                        [{/block}]
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                [{/foreach}]
                            </div>

                            <div class="clear"></div>
                        </div>
                    </div>
                </div>

            [{/foreach}]
        </div>
    [{else}]
        <div class="alert alert-primary" role="alert">
            [{oxmultilang ident="D3_DATAWIZARD_ERR_NOEXPORT_INSTALLED"}]
        </div>
    [{/if}]
</form>

<div id="mask" class=""></div>
<div id="popup2" class="d3loader-2">
    <div class="d3loader-spinner">
        <div class="d3loader-circle-1"></div>
        <div class="d3loader-circle-2"></div>
        <div class="d3loader-circle-3"></div>
    </div>
</div>

[{include file="d3_cfg_mod_inc.tpl"}]