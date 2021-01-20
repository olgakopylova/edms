<div class="content-show">
    <form id="doc_form" method="get" class="needs-validation" autocomplete="on">
        {if errors_none}<div class="alert alert-success" role="alert">{#errors_none}</div>{/if}
        {if date_err}<div class="alert alert-danger" role="alert">{#date_err}</div>{/if}
        <input id="id" name="id" value="{#id}" hidden>
        <input id="{#type}" name="{#type}" value="1" hidden>
        <div class="row">
            <div class="form-group col-md-12 required">
                <label for="id_boss" class="control-label small">Кого замещает</label>
                <select name="id_boss" class="selectpicker form-control" id="id_boss" {if disabled}disabled{/if}>{#rows1}</select>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12 required">
                <label for="id_alternate" class="control-label small">Кто замещает</label>
                <select name="id_alternate" class="selectpicker form-control" data-live-search="true" id="id_alternate" {if disabled}disabled{/if}>{#rows2}</select>
                {if alternate_err}<div class="invalid-feedback" id="id_alternate">{#alternate_err}</div>{/if}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12 required">
                <label for="id_alternate" class="control-label small">Тип замещения</label>
                <select name="type" class="selectpicker form-control" id="type" {if disabled}disabled{/if}>
                    <option value="0">Исполняющий обязанности</option><option value="1">Помощник</option></select>
                {if alternate_err}<div class="invalid-feedback" id="type">{#type_err}</div>{/if}
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6 required">
                <label for="date_start" class="control-label small">Дата начала</label>
                <input type="date" id="date_start" name="date_start" class="form-control {if date_start_err}is-invalid{/if}" value="{#date_start}"  {if type}disabled{/if}>
            </div>
            <div class="form-group col-md-6 required">
                <label for="date_end" class="control-label small">Дата окончания</label>
                <input type="date" id="date_end" name="date_end" class="form-control {if date_end_err}is-invalid{/if}" value="{#date_end}" required {if type}disabled{/if}>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="mode" class="control-label small">Постоянное замещение</label>
                <input class="float-left" type="checkbox" id="mode" name="mode" value="1" {if type}checked{/if}>
            </div>
        </div>
        <div style="text-align: right;">
            <button type="submit" id="assignment/save" class="btn btn-outline-success">Сохранить</button>
        </div>
    </form>
</div>
<style>
    .invalid-feedback {
        display: initial;
    }
    #id_alternate_chosen .chosen-single{
    {if alternate_err} border: 1px solid #dc3545;{/if}
    }
</style>
<script>

    $(document).ready(function () {
        $('.selectpicker').selectpicker({
            noneResultsText: "Совпадений не найдено!",
            noneSelectedText: 'Выберите'
        });
        var proc_one = false;
        var temp = "";
        $('.bs-searchbox')[0].children[0].addEventListener("input", function () {
            var el = this;
            if (proc_one === false) {
                selectedValues = 0;
                temp = this.value;
                if((el.value.length%3) ==0) {
                    proc_one = true;
                    $.ajax({
                        type: "POST",
                        url: "/ajax/edms/search/{#user}",
                        data: {users: selectedValues,id_user:temp},
                        dataType: "json",
                        success: function (data) {
                            proc_one = false;
                            $('#id_alternate').html(data['rows']).selectpicker('refresh');
                            el.value = temp;
                        },
                    });
                }
            } else
                el.value = temp;
        });
    });
    var checkbox = document.getElementById('mode');
    checkbox.addEventListener( 'change', function() {
        if(this.checked) {
            document.getElementById('date_start').setAttribute('disabled','disabled');
            document.getElementById('date_end').setAttribute('disabled','disabled');
        }else{
            document.getElementById('date_start').removeAttribute('disabled');
            document.getElementById('date_end').removeAttribute('disabled');
        }
    });
    $('#id_alternate').change(function () {
        var selectedValues = [];
        $("#id_alternate :selected").each(function(){
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
                $('#id_alternate').html(data['rows']).selectpicker('refresh');
            }
        });
        //здесь еще добавить стандартную начальную подгрузку
    });

</script>
