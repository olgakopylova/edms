{if errors_none}<div class="alert alert-success" role="alert">{#errors_none}</div>{/if}
{if date_end_err}<div class="alert alert-danger" role="alert">{#date_end_err}</div>{/if}
<div class="row" {if close}hidden{/if}>
    <div class="form-group col-md-12">
        <label for="id_type" class="control-label small">Документ</label>
        <input type="id_prot" autocomplete="off" id="name" name="name" class="form-control" value='{#name}' required disabled>
    </div>
</div>
<div id="test">{#body}</div>
<script>
    $('#id_type').chosen({
        theme: "bootstrap"
    });
</script>
