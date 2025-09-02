<div class="col-sm-6 col-md-4 col-lg-3 pb-4">
    <div class="card">
        <h5 class="card-header">
            [{$item->getTitle()}]
        </h5>
        <div class="card-body">

            <form name="myedit" id="form_[{$id}]" action="[{$oViewConf->getSelfLink()}]" method="post">
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="[{$oViewConf->getActiveClassName()}]">
                <input type="hidden" name="fnc" value="runTask">
                <input type="hidden" name="taskid" value="[{$id}]">
                <input type="hidden" name="format" value="CSV">


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
                <p class="card-text collapse" id="collapseExample_[{$id}]">
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