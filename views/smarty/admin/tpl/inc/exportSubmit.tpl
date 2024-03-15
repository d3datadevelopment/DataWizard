[{block name="submitElements"}]
    <div class="btn-group" [{$readonly}]>
        <button type="button" class="btn btn-primary" onclick="startTask('[{$id}]', 'CSV')" [{$readonly}]>
            <i class="fas fa-fw fa-magic"></i>
            [{oxmultilang ident=$item->getButtonText()}]
        </button>
        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" [{$readonly}]>
            <span class="sr-only">
                <i class="fas fa-fw fa-magic"></i>
                [{oxmultilang ident=$item->getButtonText()}]
            </span>
        </button>
        <div class="dropdown-menu">
            [{block name="dataWizardFormat"}]
                [{assign var="rendererBridge" value=$item->getRendererBridge()}]
                [{foreach from=$rendererBridge->getTranslatedRendererIdList() key="key" item="translationId"}]
                    <button class="dropdown-item" onclick="startTask('[{$id}]', '[{$key}]')" [{$readonly}]>
                        [{oxmultilang ident=$translationId}]
                    </button>
                [{/foreach}]
            [{/block}]
        </div>
    </div>
[{/block}]