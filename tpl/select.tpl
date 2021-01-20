{if errors_none}<div class="alert alert-success" role="alert">{#errors_none}</div>{/if}
{if date_end_err}<div class="alert alert-danger" role="alert">{#date_end_err}</div>{/if}
<div class="row" {if close}hidden{/if}>
    <div class="form-group col-md-12">
        <label for="id_type" class="control-label small">Тип документа</label>
        <select name="id_type" class="selectpicker form-control" data-live-search="true" data-selected-text-format="count" id="id_type" {if disabled} disabled {/if}>{#rows1}</select>
    </div>
</div>
<div id="test">{#body}</div>

<script>
    $(document).ready(function () {
        $('.selectpicker').selectpicker({
            noneResultsText: "Совпадений не найдено!",
            noneSelectedText: 'Выберите'
        });
    });
    $('#id_type').change(function () {
        if(document.getElementById('id_type').value==2)
            $('#row_prot').prop("hidden",false);
        else
            $('#row_prot').prop("hidden",true);
    });
</script>