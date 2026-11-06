[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{oxstyle include=$oViewConf->getModuleUrl('d3datawizard', 'out/admin/src/css/bootstrap.min.css')}]
[{oxscript include=$oViewConf->getModuleUrl('d3datawizard', 'out/admin/src/js/bootstrap.bundle.min.js')}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

<style>
    button {
        margin: 1em 1em 1em 5em;
    }
    html {
        font-size: 0.8em;
    }
    /* Image courtesy of gradientmagic.com */
    body {
        background-image: [{$d3dw_backgroundimage}];
    }
    h4 .btn {
        font-size: 1.3rem;
    }
    h5.card-header {
        font-size: 1.1rem;
    }
    .formElements label {
        display: inline-block;
        margin: .5rem 0;
    }
    .errorbox {
        position: relative;
        padding: 1rem;
        margin: 0 0 1rem;
        color: #842029;
        background-color: #f8d7da;
        border: 1px solid #f5c2c7;
        border-radius: .25rem;
        font-weight: normal;
    }
    .errorbox p:last-child {
        margin-bottom: 0;
    }
</style>

[{capture name="d3script"}][{strip}]
    function startTask(id, format = '') {
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
        form = document.getElementById('form_' + id);
        form.format.value = format;
        form.submit();
    }
[{/strip}][{/capture}]
[{oxscript add=$smarty.capture.d3script}]

[{if isset($actionSuccessMessage) && $actionSuccessMessage}]
    <div class="alert alert-success" role="status">[{$actionSuccessMessage|escape}]</div>
[{/if}]

[{if $oView->getQueryPreview() !== null}]
    <div class="alert alert-info" role="status">
        <strong>[{oxmultilang ident="D3_DATAWIZARD_QUERY_PREVIEW"}]</strong>
        <pre>[{$oView->getQueryPreview()|escape}]</pre>
    </div>
[{/if}]

[{assign var="groups" value=$oView->getGroups()}]
[{if $groups|@count}]
    <div id="accordion">
        [{foreach from=$oView->getGroups() item="group"}]
            <div class="card mb-2">
                <div class="card-header p-1" id="heading[{$group}]">
                    <h4 class="mb-0">
                        <span class="btn p-1" data-bs-toggle="collapse" data-bs-target="#collapse[{$group}]" aria-expanded="false" aria-controls="collapse[{$group}]">
                            [{oxmultilang ident=$group}]
                        </span>
                    </h4>
                </div>

                <div id="collapse[{$group}]" class="collapse" aria-labelledby="heading[{$group}]" data-bs-parent="#accordion">
                    <div class="card-body pb-0">
                        <div class="row">
                            [{foreach from=$oView->getGroupTasks($group) key="id" item="item"}]
                                [{include file="@d3datawizard/admin/inc/wizardDefaultBox.tpl" submit=$submit}]
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
        [{oxmultilang ident=$d3dw_noitemmessageid}]
    </div>
[{/if}]

<div id="mask" class=""></div>
<div id="popup2" class="d3loader-2">
    <div class="d3loader-spinner">
        <div class="d3loader-circle-1"></div>
        <div class="d3loader-circle-2"></div>
        <div class="d3loader-circle-3"></div>
    </div>
</div>

[{include file="@d3modcfg_lib/admin/inc/inc.tpl"}]
