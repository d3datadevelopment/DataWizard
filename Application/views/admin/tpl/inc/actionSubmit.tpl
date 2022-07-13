[{block name="submitElements"}]
    <div class="btn-group">
        <button type="button" class="btn btn-primary" onclick="if (confirm('[{oxmultilang ident="D3_DATAWIZARD_ACTION_SUBMIT_CONFIRM"}]') === true) {startTask('[{$id}]')}">
            <i class="fas fa-fw fa-magic"></i>
            [{oxmultilang ident=$item->getButtonText()}]
        </button>
    </div>
[{/block}]