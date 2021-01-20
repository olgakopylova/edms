<input id="{#type}" name="{#type}" value="1" hidden>
<input id="id" name="id" value="{#id}" hidden>
<div class="row" id="row_prot" {if docName}hidden{/if}>
    <div class="form-group col-md-12">
        <label for="id_prot" class="control-label small">Документ</label>
        <select name="id_prot" class="selectpicker form-control"  id="id_prot" disabled><option value=""></option>{#rows0}</select>
    </div>
</div>
<div class="row" id="row_prot" {if !docName}hidden{/if}>
    <div class="form-group col-md-12">
        <label for="id_type" class="control-label small">Документ</label>
        <div class="alert alert-secondary" role="alert"><a href="/edms/517649557/errand/{#id_doc}">{#docName}</a></div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        <label for="id_type" class="control-label small">Тип</label>
        <select name="id_type" class="selectpicker form-control" id="id_type" disabled>{#rows5}</select>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6 required">
        <label for="from" class="control-label small">Инициатор</label>
        <select name="from" class="selectpicker form-control" id="from" data-live-search="true" {if lock}disabled{/if}>{#rows1}</select>
    </div>
    <div class="form-group col-md-6 required">
        <label for="id_user" class="control-label small">Согласующие</label>
        {if !lock}<select name="id_user[]" id="id_user" class="selectpicker form-control" data-live-search="true" multiple>{#rows2}</select>{/if}
        {if lock}<select name="id_user[]" id="id_user" class="selectpicker form-control" data-live-search="true" multiple disabled>{#rows2}</select>{/if}
        {if user_err}<div class="invalid-feedback" id="id_user">{#user_err}</div>{/if}
    </div>
</div>
<div class="row">
    <div class="col-md-6"></div>
    <div class="form-group col-md-6">
        <label for="id_resp" class="control-label  small">Утверждающая подпись</label>
        <select name="id_resp" class="selectpicker form-control" data-live-search="true" id="id_resp" class="form-control"  {if lock}disabled{/if}>{#rows4}</select>
    </div>
</div>
<div class="row">
    <div class="col-md-6"></div>
    <div class="form-group col-md-6">
        <label for="id_watcher" class="control-label small">Наблюдатели</label>
        <select name="id_watcher[]" class="selectpicker form-control" id="id_watcher" data-live-search="true" multiple {if lock}disabled{/if}>{#rows3}</select>
    </div>
</div>
<div class="row">
    <div class="col-md-6"></div>
    <div class="form-group col-md-6">
        <label for="is_tracked" class="control-label small">Последовательное подписание</label>
        <input class="float-left" type="checkbox" id="is_consistent" name="is_consistent" value="1" {if checked}checked{/if} {if disabled}disabled{/if}>
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
        <label for="text" class="control-label small">Описание</label>
        <textarea id="text" name="text" class="form-control" {if disabled}disabled{/if}>{#text}</textarea>
    </div>
</div>
<div class="row">
    {if !disabled}{#addFile}{/if}
</div>

<!--<div class="row">
    <div class="form-group col-md-12">
        <label for="is_tracked" class="control-label small">Без отслеживания выполнения</label>
        <input class="float-left" type="checkbox" id="is_tracked" name="is_tracked" value="0" {if checked}checked{/if} {if disabled}disabled{/if}>
    </div>
</div>-->

<script>
    $('#text').summernote({
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']]
        ]});
    $(function(){
        {if disabled}$('#text').summernote('disable');{/if}
    });
    $(document).ready(function () {
        var proc_one=false;
        var proc_two=false;
        var temp="";
        $('.selectpicker').selectpicker({
            noneResultsText: "Совпадений не найдено!",
            noneSelectedText : 'Выберите'
        });
        $('.bs-searchbox')[1].children[0].addEventListener("input", function() {
            var el=this;
            var selectedValues = [];
            if(proc_one===false){
                $("#id_user :selected").each(function(){
                    selectedValues.push($(this).val());
                });
                if(selectedValues.length==0)
                    selectedValues=0;
                temp=this.value;
                if((el.value.length%3) ==0) {
                    proc_one=true;
                    $.ajax({
                        type: "POST",
                        url: "/ajax/edms/search/{#user}",
                        data: {users: selectedValues,id_user:temp},
                        dataType: "json",
                        success: function(data) {
                            proc_one=false;
                            $('#id_user').html(data['rows']).selectpicker('refresh');
                            el.value=temp;
                        },
                    });
                }
            }else
                el.value=temp;
        });
        $('.bs-searchbox')[3].children[0].addEventListener("input", function() {
            var el=this;
            var selectedValues = [];
            if(proc_two===false){
                var temp=this.value;
                if((el.value.length%3) ==0) {
                    $("#id_watcher :selected").each(function(){
                        selectedValues.push($(this).val());
                    });
                    if(selectedValues.length==0)
                        selectedValues=0;
                    proc_two=true;
                    $.ajax({
                        type: "POST",
                        url: "/ajax/edms/search/{#user}",
                        data: {users: selectedValues,id_user:temp},
                        dataType: "json",
                        success: function(data) {
                            proc_two=false;
                            $('#id_watcher').html(data['rows']).selectpicker('refresh');
                            el.value=temp;
                        },
                    });
                }
            }else
                el.value=temp;
        });
    });
    $('#from').change(function () {
        $.ajax({
            url: "/ajax/edms/documentation/change/user/"+this.value,
            type: "POST",
            dataType: "JSON",
            success: function (data) {
                $('#id_user').html(data['option']).selectpicker('refresh');
                $('#id_watcher').html(data['option']).selectpicker('refresh');
            }
        });
    });
    $('#id_user').change(function () {
        var selectedValues = [];
        $("#id_user :selected").each(function(){
            selectedValues.push($(this).val());
        });
        if(selectedValues.length==0)
            selectedValues=0;
        $.ajax({
            url: "/ajax/edms/documentation/resp/user/{#user}",
            type: "POST",
            data:{id_resp: selectedValues},
            dataType: "JSON",
            success: function (data) {
                if(selectedValues!=0)
                    $('#id_resp').html(data['option']).prop("selected", 1).selectpicker('refresh');
                else
                    $('#id_resp').html(null).selectpicker('refresh');
                $('#id_user').html(data['rows']).selectpicker('refresh')
            }
        });
    });
</script>