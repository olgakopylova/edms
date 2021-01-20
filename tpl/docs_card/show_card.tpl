<div class="content-show">
    <form id="doc_form" method="get" class="needs-validation" autocomplete="on">
        <div class="row row-conformity">
            <div class="col-sm-9">
                {#body}
            </div>
            <div class="col-sm-3">
                {#buttons}
            </div>
        </div>
        {#files}

        <div style="text-align: right;">
            <button type="submit" id="doc" onclick = "" class="btn btn-sm btn-outline-success" {if mode} hidden{/if}>Сохранить изменения</button>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label for="historyTable" class="control-label small">История изменений</label>
                {#history}
            </div>
        </div>

    </form>
</div>


<script>
    $(document).ready(function () {
        $('.row-conformity > [class*=col-]').conformity();
        $(window).on('resize', function() {
            $('.row-conformity > [class*=col-]').conformity();
        });

    });


</script>



