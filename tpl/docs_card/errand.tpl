<input id="ernd" name="ernd" value="1" hidden>
<input id="id" name="id" value="{#id}" hidden>
{if errors_none}<div class="alert alert-success" role="alert">{#errors_none}</div>{/if}
{if date_end_err}<div class="alert alert-danger" role="alert">{#date_end_err}</div>{/if}
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="id_user1" class="control-label small">Исполнитель</label>
        <select name="id_user1[]" id="id_user1" class="selectpicker form-control" data-live-search="true" multiple {if suppl}disabled{/if}>{#rows2}</select>
        {if user_err}<div class="invalid-feedback">{#user_err}</div>{/if}
    </div>
</div>
<div class="row">
    <div class="col-md-6"></div>
    <div class="form-group col-md-12">
        <label for="id_resp" class="control-label  small">Ответственный исполнитель</label>
        <select name="id_resp1" id="id_resp1" class="selectpicker form-control" data-live-search="true" {if not_mult}disabled{/if}>{#rows4}</select>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        <label for="id_watcher1" class="control-label small">Наблюдатель</label>
        <select name="id_watcher1[]" id="id_watcher1" class="selectpicker form-control" data-live-search="true" multiple>{#rows3}</select>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12 required">
        <label for="name" class="control-label small">Наименование</label>
        <input type="text" id="name" name="name" class="form-control {if name_err}is-invalid{/if}" value='{#name}' required>
        {if name_err}<div class="invalid-feedback">{#name_err}</div>{/if}
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        <label for="text2" class="control-label small">Описание</label>
        <textarea id="text2" name="text2" class="form-control {if text_err}is-invalid{/if}" {if disabled}disabled{/if}>{#text}</textarea>
        {if text_err}<div class="invalid-feedback">{#text_err}</div>{/if}
    </div>
</div>
<div class="row" {if close}hidden{/if}>
    <div class="form-group col-md-12">
        <label for="is_tracked1" class="control-label small">Без отслеживания выполнения</label>
        <input class="float-left" type="checkbox" id="is_tracked1" name="is_tracked1" value="0" {if checked}checked{/if} {if disabled}disabled{/if}>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6 required">
        <label for="date_end" class="control-label small">Дата окончания</label>
        <input type="date" id="date_end" name="date_end" class="form-control {if date_end_err}is-invalid{/if}" value="{#date_end}" required>
    </div>
    {#addFile}
</div>

<style>
    #id_user1_chosen > ul {
    {if user_err} border: 1px solid #dc3545;{/if}
    }
    #text2 > div {
    {if text_err} border: 1px solid #dc3545;{/if}
    }
</style>

<script>
    $('.selectpicker').selectpicker({
        noneResultsText: "Совпадений не найдено!",
        noneSelectedText : 'Выберите'
    });
    $('#text2').summernote({
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
        {if disabled}$('#text2').summernote('disable');{/if}
    });

    $('#id_user1').change(function () {
        var selectedValues = [];
        $("#id_user1 :selected").each(function(){
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
                    $('#id_resp1').html(data['option']).prop("selected", 1).selectpicker('refresh');
                else
                    $('#id_resp1').html(null).selectpicker('refresh');
                $('#id_user1').html(data['rows']).selectpicker('refresh');
            }
        });
        //здесь еще добавить стандартную начальную подгрузку
    });
    $('#id_watcher1').change(function () {
        var selectedValues = [];
        $("#id_watcher1 :selected").each(function(){
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
                $('#id_watcher1').html(data['rows']).selectpicker('refresh');
            }
        });
    });
    var proc_one=false;
    var proc_two=false;
    var temp="";
    var count=$('.bs-searchbox').length;
    $('.bs-searchbox')[count-3].children[0].addEventListener("input", function() {
        var el=this;
        var selectedValues = [];
        if(proc_one===false){
            $("#id_user1 :selected").each(function(){
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
                        $('#id_user1').html(data['rows']).selectpicker('refresh');
                        el.value=temp;
                    },
                });
            }
        }else
            el.value=temp;
    });
    $('.bs-searchbox')[count-1].children[0].addEventListener("input", function() {
        var el=this;
        var selectedValues = [];
        if(proc_two===false){
            var temp=this.value;
            if((el.value.length%3) ==0) {
                $("#id_watcher1 :selected").each(function(){
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
                        $('#id_watcher1').html(data['rows']).selectpicker('refresh');
                        el.value=temp;
                    },
                });
            }
        }else
            el.value=temp;
    });
</script>