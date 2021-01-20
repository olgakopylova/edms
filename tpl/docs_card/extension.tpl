<input id="{#type}" name="{#type}" value="1" hidden>
<input name="id" id="id" value="{#id}" hidden>
{if dop}<input name="dop" id="dop" value="{#dop}" hidden>{/if}
{if errors_none}<div class="alert alert-success" role="alert">{#errors_none}</div>{/if}
{if err}<div class="alert alert-danger" role="alert">{#err}</div>{/if}
{if date_end_err}<div class="alert alert-danger" role="alert">{#date_end_err}</div>{/if}
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="name" class="control-label small">Наименование</label>
        <input type="text" id="name_ext" name="name_ext" class="form-control" value='{#name}' disabled>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="text2" class="control-label small">Сообщение</label>
        <textarea id="text2{#ext}" name="text2" class="form-control {if text_err}is-invalid{/if}" {if edit} disabled{/if}{if close} disabled{/if}>{#text}</textarea>
        {if text_err}<div class="invalid-feedback">{#text_err}</div>{/if}
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6 required">
        <label for="date_end" class="control-label small">Новая дата окончания</label>
        <input type="date" id="date_end" name="date_end" class="form-control {if date_end_err}is-invalid{/if}" value="{#date_end}" required {if close} disabled{/if}>
    </div>
    {#addFile}
</div>
<style>
    #text2 > div {
    {if text_err} border: 1px solid #dc3545;{/if}
    }
</style>

<script>
    $('#text2{#ext}').summernote({
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
        ]
    });
    $(function(){
        {if close}$('#text2{#ext}').summernote('disable');{/if}
        {if edit}$('#text2{#ext}').summernote('disable');{/if}
    });
</script>