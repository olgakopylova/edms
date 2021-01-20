<div class="row" >
    <div class="form-group col-md-12 small">
        <div {if !sgn}hidden{/if}>
            <p><strong> {#sgn}</strong></p>
        </div>
        <div {if !mainsgn}hidden{/if}>
            <p><strong>Утверждающая подпись:</strong></p>
                <p>{#mainsgn}</p>
            <p><strong>Согласовано:</strong></p>
                <p>{#other}</p>
            {#filesgn}
        </div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12" {if !text} hidden{/if}>
        <label for="text" class="control-label small">Описание</label>
        <textarea id="text" name="text" class="form-control" disabled>{#text}</textarea>
    </div>
    <div class="form-group col-md-12" {if !ext}hidden{/if}>
        <label for="date_end" class="control-label small">Новая дата окончания</label>
        <input type="date" id="date_end" name="date_end" class="form-control" value="{#date_end}" disabled>
    </div>
    <div class="form-group col-md-12">
        <label for="date_first" class="control-label small">Дата создания</label>
        <div class="alert alert-secondary" role="alert">{#date_first_full}</div>
    </div>
</div>
<div class="form-group"{if !btn}hidden{/if}>
    <label for="" class="control-label small">Действия</label>
    {#btn}
</div>
{if files}
    {#files}
{/if}



<script>
    $('#text').summernote({
        toolbar: [
        ]
    });
    $('#text').summernote('disable');
</script>