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
        background-image: linear-gradient(22.5deg, rgba(66, 66, 66, 0.02) 0%, rgba(66, 66, 66, 0.02) 11%,rgba(135, 135, 135, 0.02) 11%, rgba(135, 135, 135, 0.02) 24%,rgba(29, 29, 29, 0.02) 24%, rgba(29, 29, 29, 0.02) 38%,rgba(15, 15, 15, 0.02) 38%, rgba(15, 15, 15, 0.02) 50%,rgba(180, 180, 180, 0.02) 50%, rgba(180, 180, 180, 0.02) 77%,rgba(205, 205, 205, 0.02) 77%, rgba(205, 205, 205, 0.02) 100%),linear-gradient(67.5deg, rgba(10, 10, 10, 0.02) 0%, rgba(10, 10, 10, 0.02) 22%,rgba(52, 52, 52, 0.02) 22%, rgba(52, 52, 52, 0.02) 29%,rgba(203, 203, 203, 0.02) 29%, rgba(203, 203, 203, 0.02) 30%,rgba(69, 69, 69, 0.02) 30%, rgba(69, 69, 69, 0.02) 75%,rgba(231, 231, 231, 0.02) 75%, rgba(231, 231, 231, 0.02) 95%,rgba(138, 138, 138, 0.02) 95%, rgba(138, 138, 138, 0.02) 100%),linear-gradient(112.5deg, rgba(221, 221, 221, 0.02) 0%, rgba(221, 221, 221, 0.02) 17%,rgba(190, 190, 190, 0.02) 17%, rgba(190, 190, 190, 0.02) 39%,rgba(186, 186, 186, 0.02) 39%, rgba(186, 186, 186, 0.02) 66%,rgba(191, 191, 191, 0.02) 66%, rgba(191, 191, 191, 0.02) 68%,rgba(16, 16, 16, 0.02) 68%, rgba(16, 16, 16, 0.02) 70%,rgba(94, 94, 94, 0.02) 70%, rgba(94, 94, 94, 0.02) 100%),linear-gradient(90deg, #ffffff,#ffffff);
    }
    h4 .btn {
        font-size: 1.3rem;
    }
    h5.card-header {
        font-size: 1.1rem;
    }
</style>

[{capture name="d3script"}][{strip}]
    function startAction(id) {
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
        document.getElementById('actionid').value = id;
        document.getElementById('myedit').submit();
    }
[{/strip}][{/capture}]
[{oxscript add=$smarty.capture.d3script}]

<form name="myedit" id="myedit" action="[{$oViewConf->getSelfLink()}]" method="post" style="padding: 0;margin: 0;height:0;">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
    <input type="hidden" name="fnc" value="doAction">
    <input type="hidden" name="actionid" id="actionid" value="">

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
                                [{foreach from=$oView->getGroupActions($group) key="id" item="action"}]
                                    <div class="col-sm-6 col-md-4 col-lg-3 pb-4">
                                        <div class="card">
                                            <h5 class="card-header">
                                                [{$action->getTitle()}]
                                            </h5>
                                            <div class="card-body">
                                                <p class="card-text">
                                                    [{$action->getDescription()}]
                                                </p>

                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-primary" onclick="startAction('[{$id}]')">
                                                        <i class="fas fa-magic"></i>
                                                        [{oxmultilang ident=$action->getButtonText()}]
                                                    </button>
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
            [{oxmultilang ident="D3_DATAWIZARD_ERR_NOACTION_INSTALLED"}]
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