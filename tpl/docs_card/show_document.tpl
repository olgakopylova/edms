<div class="content-show">
    <form id="doc_form" method="get" class="needs-validation" autocomplete="on">
        <div class="row">
            <div class="col-md-8">
                {#body}
            </div>
            <div class="col-md-4">
                {#buttons}
            </div>
        </div>
        {#files}
        <div class="row" style="margin-top: 15px;" {if !table} hidden{/if}>
            <div class="form-group col-md-12">
                <label for="text" class="control-label small">Поручения</label>
                {#table}
            </div>
        </div>
        <div style="text-align: right;">
            <button type="submit" id="doc" onclick = "" class="btn btn-outline-success btn-sm" {if disabled} hidden {/if}>{if !sign}Сохранить{/if}{if sign}Отправить на подпись{/if}</button>
        </div>
    </form>

</div>


<style>
    .content-show{
        padding: 19px;
        margin-bottom: 20px;
        margin-top: 10px;
        background-color: #f5f5f5;
        border: 1px solid #e3e3e3;
    }
</style>