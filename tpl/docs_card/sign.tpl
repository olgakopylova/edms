<input id="{#type}" name="{#type}" value="1" hidden>
<input name="id" id="id" value="{#id}" hidden>
<input name="mode" id="mode" value="{#mode}" hidden>
{if errors_none}<div class="alert alert-success" role="alert">{#errors_none}</div>{/if}
{if errors}<div class="alert alert-danger" role="alert">{#errors}</div>{/if}
<div class="row">
    <div class="form-group col-md-12">
        <label for="{#t}" class="control-label small">Сообщение</label>
        <textarea id="{#t}" name="{#t}" class="form-control" {if disabled}disabled{/if}>{#text}</textarea>
    </div>
</div>
<div class="row" {if hide} hidden{/if}>
    {#addFile}
</div>
<div class="row" {if hide} hidden{/if}>
    <div class="form-group col-md-4 required">
        <label for="pin" class="control-label small">Пин-код</label>
        <input id="pin" name="pin" type="password" maxlength="4" minlength="4" class="form-control init-position"></input>
    </div>
</div>

<script>
    $('#{#t}').summernote({
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
        {if disabled}$('#{#t}').summernote('disable');{/if}
    });
</script>