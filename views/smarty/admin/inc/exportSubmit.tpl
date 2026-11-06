[{block name="submitElements"}]
    <div class="btn-group" [{$readonly}]>
        <button type="button" class="btn btn-primary" onclick="startTask('[{$id}]', 'CSV_SPREADSHEET')" [{$readonly}]>
            <i class="fas fa-fw fa-magic"></i>
            [{assign var="buttonText" value=$item->getButtonText()|oxmultilangassign}]
            [{assign var="formatText" value="D3_DATAWIZARD_EXPORT_FORMAT_CSV_SPREADSHEET"|oxmultilangassign}]
            [{oxmultilang ident="D3_DATAWIZARD_EXPORT_SUBMIT_FORMAT" args=[$buttonText, $formatText]}]
        </button>
        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" [{$readonly}]>
            <span class="visually-hidden">
                [{oxmultilang ident="D3_DATAWIZARD_EXPORT_OPTIONS"}]
            </span>
        </button>
        <div class="dropdown-menu">
            [{block name="dataWizardFormat"}]
                [{assign var="rendererBridge" value=$item->getRendererBridge()}]
                [{foreach from=$rendererBridge->getTranslatedRendererIdList() key="key" item="translationId"}]
                    <button type="button" class="dropdown-item" onclick="startTask('[{$id}]', '[{$key}]')" [{$readonly}]>
                        [{oxmultilang ident=$translationId}]
                    </button>
                [{/foreach}]
                <button type="button" class="dropdown-item" onclick="startTask('[{$id}]', 'QUERY')" [{$readonly}]>
                    [{oxmultilang ident="D3_DATAWIZARD_QUERY_PREVIEW"}]
                </button>
            [{/block}]
        </div>
    </div>
[{/block}]
