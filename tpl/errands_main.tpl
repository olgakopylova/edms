<div class="row">
    <div class="col-md-2">
        <div class="widget">
            <p class="h5">Категории</p>
            <ul class="widget-list" id="widget">
                <li onclick="Tree(this)" id="new" class="act"><a class="р5">Новые<span class="badge badge-secondary">{#new}</span></a></li>
                <li onclick="Tree(this)" id="all"><a class="р5" >Все<span class="badge">{#all}</span></a></li>
                <li onclick="Tree(this)" id="inwork"><a class="р5">В работе<span class="badge">{#inwork}</span></a></li>
                <li onclick="Tree(this)" id="past"><a class="р5">Просроченные<span class="badge">{#past}</span></a></li>
                <li onclick="Op(this)"  value="0"><a class="р5">Скоро</a><br><div id="soon_val" style="display: none;"><a class="р5" onclick="Tree(this,true)" id="1">1 день</a><a class="р5"  onclick="Tree(this,true)" id="2">2 дня</a><a class="р5"  onclick="Tree(this,true)" id="3">3 дня</a></div></li>
                <li onclick="Tree(this)" id="done"><a class="р5">Исполненные<span class="badge">{#done}</span></a></li>
            </ul>
        </div>
    </div>

    <div class="col-md-7">
        <div class="row">
            <div class="col-2">
                <a data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">Фильтр</a>
            </div>
            <div class="col-10" style="align-self: center;">
                <input id="hide" value="0" hidden>
                <a onclick="Hide(this)" id="hidebtn" style="float: right; cursor: pointer;margin-right: 5px;">Развернуть все</a>
            </div>
        </div>
        <div class="collapse" id="collapseExample">
            <div class="card card-body">
                <form  method="post" class="needs-validation" autocomplete="on">
                    <div class="col-md-12">
                        <input id="type" value="e" hidden>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="user_from" class="control-label small">Поиск</label>
                                <input type="text" id="find" name="find" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="user_from" class="control-label small">С</label>
                                <input type="date" id="date_start" name="date_start" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="user_from" class="control-label small">По</label>
                                <input type="date" id="date_end" name="date_end" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="user_from" class="control-label small">Инициатор</label>
                                <input type="text" id="own" name="own" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="user_from" class="control-label small">Исполнитель</label>
                                <input type="text" id="us" name="us" class="form-control">
                            </div>
                        </div>
                        <button type="submit" id="filter" class="btn btn-sm btn-outline-primary float-right btn-filter">Применить</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="form-group content" id="tree">
            {#tree}
        </div>
        <div class="btn-group btn-group-sm" role="group" id="pagination">
            {#pagination}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group" id="preview" style="display: none;">
        </div>
    </div>
</div>

<style>
    .btn-outline-secondary {
        border: 1px solid #aaa !important;
    }
    #preview{
        padding: 10px;
        margin-bottom: 20px;
        background-color: #f5f5f5;
        border: 1px solid #e3e3e3;
    }
    li{
        cursor: pointer;
    }
    .top{
        margin-left: 2%;
    }
    * {box-sizing: border-box; margin: 0;}
    .widget {
        background: #fff;
        border-radius: 5px;
        font-family: 'Roboto', sans-serif;
    }
    .widget h3 {
        margin-bottom: 20px;
        text-align: center;
        font-size: 24px;
        font-weight: normal;
        color:  #424949;
    }
    .widget ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .act{
        background-color: #f5f5f5;
    }
    .widget li {
        border-bottom: 1px solid #eaeaea;
        padding-bottom: 15px;
        text-decoration: none;
        padding-left: 2%;
    }
    .widget li:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .widget a {
        width: 100%;
        text-decoration: none;
        color:  #616a6b;
        display: inline-block;
        padding: 5px;
    }
    .widget li:before {
        font-family: FontAwesome;
        font-size: 20px;
        vertical-align:bottom;
        color: #dd3333;
        margin-right: 14px;
    }
    li {
        list-style-type: none; /* Убираем маркеры */
    }
    .data-container ul{
        margin-left: 0px; !important;
    }

    .treeHTML details {
        width: 100%;
        padding-left: 15px;
        outline: none;
    }
    .treeHTML div {
        position: relative;
    }
    .treeHTML div:not(:last-child) { /* необязательно */
        border-left: 1px solid #ccc;
    }
    .treeHTML div:last-child:before { /* необязательно */
        border-left: 1px solid #ccc;
    }
    .treeHTML summary {
        position: absolute;
        top: 12px;
        left: 2%;
        cursor: pointer;
        outline: none;
    }
    #main {
        margin-top: 2px;
        padding-bottom: 10px; /* Поля вокруг текста */
        padding-top: 10px; /* Поля вокруг текста */
        min-height: 20px;
        background-color: white;
        border: 1px solid #e3e3e3;
    }
</style>


<script>
    function Open(obj) {
        if(obj.id=="node"){
            var el= $(obj).parent().parent()[0];
            el=$(el).next();
            var el2=obj.childNodes[0].childNodes[0];
            if($(el).css('display')=="block"){
                $(el).slideUp();
                el2.classList.remove("fa-minus");
                el2.classList.add("fa-plus");
            }
            else{
                $(el).slideDown();
                el2.classList.add("fa-minus");
                el2.classList.remove("fa-plus");
            }
        }
    }
    $.fn.followTo = function (pos) {
        var $this = this,
            $window = $(window);
        if ($window .width() >= '768'){
            $window.scroll(function (e) {
                if ($window.scrollTop() > pos) {
                    $this.css({
                        position: 'fixed',
                        top: 5,
                        //marginRight: 45,
                        marginTop: 0,
                        width: '100%',
                        maxWidth: 'inherit'
                    });
                } else {
                    $this.css({
                        position: 'relative',
                        top: 0,
                        marginRight: 0,
                        marginTop: 16,
                        width: '100%',
                        maxWidth: 'none'
                    });
                }
            });
        }

    };
    $('#preview').followTo(420);
    var flag=true;
    function Op(obj) {
        document.getElementsByClassName('act')[0].classList.remove('act');
        obj.classList.add("act");
        if(flag){
            $('#soon_val').slideDown();
            flag=false;
        }
    }
    function Tree(obj,soon) {
        $('#preview').slideUp();
        document.getElementsByClassName('act')[0].classList.remove('act');
        obj.classList.add("act");
        var id=obj.id;
        document.getElementById('hide').value=0;
        $("#hidebtn").text("Развернуть все");
        if(soon)
            id="soon&val="+obj.id;
        else{
            $('#soon_val').slideUp();
            flag=true;
        }
        $.ajax({
            url: "/ajax/edms/documentation/?view="+id+"&user="+{#user}+"&page=1",
            type: "POST",
            dataType: 'json',
            success: function(data){
                $("#tree").html(data['tree']);
                $("#pagination").html(data['pagination']);
            }
        });
    }
    function Hide() {
        var mode=document.getElementById('hide').value;
        if(mode==1) {
            document.getElementById('hide').value=0;
            $("#hidebtn").text("Развернуть все");
            $('.fa-minus').each(function(i,elem) {
                elem.classList.add("fa-plus");
                elem.classList.remove("fa-minus");
            });
            $('details').each(function(i,elem) {
                $(elem).slideUp();
            });
        }
        else{
            document.getElementById('hide').value=1;
            $("#hidebtn").text("Свернуть все");
            $('.fa-plus').each(function(i,elem) {
                elem.classList.add("fa-minus");
                elem.classList.remove("fa-plus");
            });
            $('details').each(function(i,elem) {
                $(elem).slideDown();
            });
        }
    }
</script>


