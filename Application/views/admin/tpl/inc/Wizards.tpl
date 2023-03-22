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
        document.getElementById('taskid').value = id;
        document.getElementById('format').value = format;
        document.getElementById('myedit').submit();
    }
[{/strip}][{/capture}]
[{oxscript add=$smarty.capture.d3script}]

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
                            [{foreach from=$oView->getGroupTasks($group) key="id" item="item"}]
                                <div class="col-sm-6 col-md-4 col-lg-3 pb-4">
                                    <div class="card">
                                        <h5 class="card-header">
                                            [{$item->getTitle()}]
                                        </h5>
                                        <div class="card-body">

                                            <form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post">
                                                [{$oViewConf->getHiddenSid()}]
                                                <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
                                                <input type="hidden" name="fnc" value="runTask">
                                                <input type="hidden" name="taskid" id="taskid" value="">
                                                <input type="hidden" name="format" id="format" value="CSV">


                                                [{if $item->getDescription()}]
                                                    [{assign var="description" value=$item->getDescription()}]
                                                    [{assign var="sectionlength" value="100"}]

                                                    [{if $description|count_characters:true <= $sectionlength}]
                                                        <p class="card-text">[{$description}]</p>
                                                    [{else}]
                                                        [{assign var="shorttext" value=$description|truncate:$sectionlength:''}]
                                                        <p class="card-text" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample" style="cursor: pointer">
                                                            [{$shorttext}]...
                                                        </p>
                                                        <p class="card-text collapse" id="collapseExample">
                                                            ...[{$description|replace:$shorttext:''}]
                                                        </p>
                                                    [{/if}]
                                                [{/if}]

                                                [{if $item->hasFormElements()}]
                                                    [{foreach from=$item->getFormElements() item="formElement"}]
                                                        [{$formElement}]
                                                    [{/foreach}]
                                                [{/if}]

                                                [{block name="exportSubmit"}]
                                                    [{include file=$submit}]
                                                [{/block}]

                                            </form>

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

[{include file="d3_cfg_mod_inc.tpl"}]