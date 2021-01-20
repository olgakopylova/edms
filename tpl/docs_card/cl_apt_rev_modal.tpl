<input id="{#type}" name="{#type}" value="1" hidden>
<input name="id" id="id" value="{#id}" hidden>
{if errors_none}<div class="alert alert-success" role="alert">{#errors_none}</div>{/if}
{if err}<div class="alert alert-danger" role="alert">{#err}</div>{/if}
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="name2" class="control-label small">Наименование</label>
        <input type="text" id="name2" name="name2" class="form-control" value='{#name}' disabled>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="text2" class="control-label small">Сообщение</label>
        <textarea id="text2" name="text2" class="form-control {if text_err}is-invalid{/if}" required {if disabled}disabled{/if}>{#text}</textarea>
        {if text_err}<div class="invalid-feedback" id="text2">{#text_err}</div>{/if}
    </div>
</div>
{#addFile}
<style>
    #text2 > div {
    {if text_err} border: 1px solid #dc3545;{/if}
    }
</style>
<script>
    $('#text2').summernote({
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
        ]
    });
    $(function(){
        {if disabled}$('#text2').summernote('disable');{/if}
    });
</script>