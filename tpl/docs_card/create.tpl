<div class="content-show">
    <form id="doc_form" method="get" class="needs-validation" autocomplete="on">
        {#body}
        <div style="text-align: right;">
            <button type="submit" id="doc" onclick = "" class="btn btn-sm btn-outline-success" {if disabled} hidden {/if}>{if !sign}Сохранить{/if}{if sign}Отправить на подпись{/if}</button>
        </div>
    </form>
</div>