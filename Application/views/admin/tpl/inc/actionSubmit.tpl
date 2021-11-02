[{block name="submitElements"}]
    <div class="btn-group">
        <button type="button" class="btn btn-primary" onclick="startTask('[{$id}]')">
            <i class="fas fa-fw fa-magic"></i>
            [{oxmultilang ident=$item->getButtonText()}]
        </button>
    </div>
[{/block}]