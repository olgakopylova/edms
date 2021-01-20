<input id="suppl" name="suppl" value="1" hidden>
<input id="id" name="id" value="{#id}" hidden>
{if errors_none}<div class="alert alert-success" role="alert">{#errors_none}</div>{/if}
{if date_end_err}<div class="alert alert-danger" role="alert">{#date_end_err}</div>{/if}
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="name3" class="control-label small">Наименование</label>
        <input type="text" id="name3" name="name3" class="form-control" value='Дополнение задачи' disabled>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="text2" class="control-label small">Описание</label>
        <textarea id="text2" name="text2" class="form-control {if text_err}is-invalid{/if}" required {if disabled}disabled{/if}>{#text}</textarea>
        {if text_err}<div class="invalid-feedback">{#text_err}</div>{/if}
    </div>
</div>
{#addFile}

<style>
    #id_user1_chosen > ul {
    {if user_err} border: 1px solid #dc3545;{/if}
    }
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