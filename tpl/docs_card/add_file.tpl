<div class="form-group col-md-6">
    <label for="user_file" class="control-label small">Прикрепить документы</label>
    <div class="custom-file" id="file">
        <input type="file" class="custom-file-input form-control-sm" name="user_file[]" id="user_file[]" multiple>
        <label class="custom-file-label" id="fileLabel" for="validatedCustomFile">Выбрать файлы</label>
    </div>
</div>

<script>
    $('.custom-file-input').change(function (e) {
        var files = [];
        var col=0;
        for (var i = 0; i < $(this)[0].files.length; i++) {
            if(i<2)
                files.push($(this)[0].files[i].name);
            else
                col++;
        }
        if($(this)[0].files.length>2) {
            $(this).next('.custom-file-label').html(files.join(', ')+ " и еще " + String(col));
        }
        else
            $(this).next('.custom-file-label').html(files.join(', '));
    });
</script>