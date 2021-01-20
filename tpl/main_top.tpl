<div class="row">
    <p class="h4">{#fio}</p>
</div>

<div class="row">
    <p class="h5">Фильтр</p>
</div>
<div class="row border" id="filter">
    <div class="col-md-12" id="a">
        <form  method="post" class="needs-validation" autocomplete="on">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                        <label for="user_from" class="control-label">С</label>
                        <input type="date" id="date_start" name="date_start" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="user_from" class="control-label">По</label>
                        <input type="date" id="date_end" name="date_end" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="control-label">Состояние</label>
                        <select id="status" name="status" data-placeholder="Выберите" class="form-control"><option value="0">Не выбрано</option><option value="1">Просроченные</option><option value="2">Непросроченные</option><option value="3">Завершенные</option><option value="4">Исполненные</option><option value="5">Неисполненые</option></select>
                    </div>
                </div>
                <button type="submit" id="filter" class="btn btn-sm btn-outline-primary float-right">Применить</button>
            </div>
        </form>
    </div>
</div>
<div class="row justify-content-end" style="padding-bottom: 10px;margin-right: 1px;">
    <input id="hide" value="0" hidden>
    <a onclick="Hide(this)" id="hidebtn" style="float: right; cursor: pointer;">Развернуть все</a>
</div>
<script>
    $(document).on("submit", "form", function(event) {
        event.preventDefault();
        var d=new FormData(this);
        if(d.has('ext')){
            d.append('name',document.getElementById('name_ext').value);
        }
        if(d.has('closing')||d.has('rev')){
            d.append('name',document.getElementById('name_cl').value);
        }
        if(d.has('edit')){
            if($("input").is("#is_tracked")&&!d.has('is_tracked'))
                d.append('is_tracked',1);
        }
        var btn = $(this).find("button[type=submit]:focus");
        if($(btn).attr('id')=="filter") {
            $.ajax({
                url: "/ajax/common/filter/user/{#user}",
                type: "POST",
                dataType: "JSON",
                data: d,
                processData: false,
                contentType: false,
                success: function (data) {
                    $('#tree').html(data['content']);
                    document.getElementById('hide').value=0;
                    $("#hidebtn").text("Развернуть все");
                }
            });

        }
    });
</script>
