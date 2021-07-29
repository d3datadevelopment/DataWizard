<div class="btn-group">
    <button type="button" class="btn btn-primary" onclick="startTask('[{$id}]', 'CSV')">
        <i class="fas fa-magic"></i>
        [{oxmultilang ident=$item->getButtonText()}]
    </button>
    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="sr-only">
            <i class="fas fa-magic"></i>
            [{oxmultilang ident=$item->getButtonText()}]
        </span>
    </button>
    <div class="dropdown-menu">
        [{block name="dataWizardFormat"}]
            <button class="dropdown-item" onclick="startTask('[{$id}]', 'CSV')">
                [{oxmultilang ident="D3_DATAWIZARD_EXPORT_FORMAT_CSV"}]
            </button>
            <button class="dropdown-item" onclick="startTask('[{$id}]', 'Pretty')">
                [{oxmultilang ident="D3_DATAWIZARD_EXPORT_FORMAT_PRETTY"}]
            </button>
        [{/block}]
    </div>
</div>