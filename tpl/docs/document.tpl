<input name="id" id="id" value="{#id}" hidden>
<input id="{#type}" name="{#type}" value="1" hidden>
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="author" class="control-label small">Создатель документа</label>
        <select name="id_user" id="id_user" class="selectpicker form-control" data-live-search="true" {if show}disabled{/if}>{#rows1}</select>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="name" class="control-label small">Наименование</label>
        <input type="text" autocomplete="off" id="name" name="name" class="form-control {if name_err}is-invalid{/if}" value='{#name}' required {if disabled}disabled{/if}>
        {if name_err}<div class="invalid-feedback" id="name">{#name_err}</div>{/if}
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        <label for="number" class="control-label small">Номер документа</label>
        <input type="text" autocomplete="off" id="number" name="number" class="form-control" value='{#number}' {if hide}disabled{/if}>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <label for="date_first" class="control-label small">Дата создания</label>
        <input type="date" id="date_first" name="date_first" class="form-control" value="{#date_first}" disabled>
    </div>
    <div class="form-group col-md-6">
        <label for="date_create" class="control-label small">Дата присвоения номера</label>
        <input type="date" id="date_create" name="date_create" class="form-control {if date_create_err}is-invalid{/if}" value="{#date_create}" {if hide}disabled{/if}>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        <label for="text" class="control-label small">Описание</label>
        <textarea id="text" name="text" class="form-control" {if disabled}disabled{/if}>{#text}</textarea>
    </div>
</div>
<div class="row">
    {#addFile}
</div>

<script>
    $(document).ready(function() {
        $('#text').summernote({
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
        {if disabled}$('#text').summernote('disable');{/if}
    });
</script>