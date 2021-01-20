<link rel="stylesheet" href="/js/bootstrap/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
<link href="/js/fontawesome/css/all.css" rel="stylesheet"> <!--load all styles -->
<link rel="stylesheet" type="text/css" href="/styles/style-ssite.css" />
<link rel="stylesheet" type="text/css" href="/styles/style-edms.css" />
<script type="text/javascript" src="/js/ajax.js"></script>
<script type="text/javascript" src="/js/js.cookie.js"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="/js/bootstrap-select-1.13.9/dist/css/bootstrap-select.min.css">
<script src="/js/bootstrap-select-1.13.9/dist/js/bootstrap-select.min.js"></script>

<script src="/js/pagination/pagination.min.js" type="text/javascript"></script>
<link href="/js/pagination/pagination.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="/js/datatables-bootstrap4/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/js/datatables-bootstrap4/DataTables-1.10.18/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="/js/datatables-bootstrap4/Responsive-2.2.2/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="/js/datatables-bootstrap4/Buttons-1.5.4/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="/js/datatables-bootstrap4/Buttons-1.5.4/js/buttons.bootstrap4.min.js"></script>
<link rel="stylesheet" href="/js/datatables-bootstrap4/Buttons-1.5.4/css/buttons.bootstrap4.min.css"/>
<script type="text/javascript" src="/js/datatables-bootstrap4/Select-1.2.6/js/dataTables.select.js"></script>
<link rel="stylesheet" href="/js/datatables-bootstrap4/DataTables-1.10.18/css/dataTables.bootstrap4.min.css"/>
<link rel="stylesheet" href="/js/datatables-bootstrap4/Select-1.2.6/css/select.dataTables.min.css"/>
<link rel="stylesheet" href="/js/datatables-bootstrap4/Select-1.2.6/css/select.bootstrap4.min.css"/>

<script type="text/javascript" src="/js/conformity.js"></script>

<script src="/js/popper.min.js"></script>
<link href="/js/summernote/summernote-bs4.css" rel="stylesheet">
<script src="/js/summernote/summernote-bs4.js"></script>

<script src="/js/jquery.maskedinput.js"></script>
<div id="content">
    {#content}
</div>
<div id="rightsModal" class="modal hide fade in" role="dialog" data-keyboard="false" data-backdrop="static" >
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="doc_form" method="get" class="needs-validation" autocomplete="on">
                <div class="row">
                    <div class="col-md-12" id="contentRight">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="previewModal" class="modal" role="dialog" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Предпросмотр</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="innerContent">

            </div>
        </div>
    </div>
</div>

<script>
    tables_lang = {
        "decimal": ",",
        "thousands": " ",
        "lengthMenu": "Показывать _MENU_ записей",
        "zeroRecords": "Значение не найдено",
        "info": "Страница _PAGE_ из _PAGES_",
        "infoFiltered": "(найдено из _MAX_ записей)",
        "emptyTable": "Нет записей",
        "info": "Загружено _START_ - _END_ из _TOTAL_ записей",
        "infoEmpty": "Показано 0 из 0 записей",
        "infoPostFix": "",
        "loadingRecords": "Загрузка...",
        "processing": "Обработка...",
        "search": "Поиск:",
        "paginate": {
            "first": "Первая",
            "last": "Последняя",
            "next": "Следующая",
            "previous": "Предыдущая"
        },
        "aria": {
            "sortAscending": ": сортировать по возрастанию",
            "sortDescending": ": сортировать по убыванию"
        }
    };

    function closePreview() {
        if($('#previewModal').is(':visible'))
            $("#previewModal").modal('hide');
    }

    function Preview(obj){
        var $this = this,$window = $(window);
        if ($window .width() >= '768')
            $('#preview').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>').slideDown();
        else{
            $("#previewModal").modal('show');
            $('#innerContent').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
        }
        $.ajax({
            type: 'POST',
            url: "/ajax/edms/documentation/preview/{#user}/id/"+obj.id,
            dataType: "json",
            success: function (data) {
                if ($window .width() >= '768')
                    $('#preview').html(data['content']).slideDown();
                else
                    $('#innerContent').html(data['content']);
            }
        });
    }

    $( document ).ready(function() {
        $("#fromMe").DataTable();
        $("#documents").DataTable();
    });

    function Delete(id) {
        $.ajax({
            type: 'POST',
            url: "/ajax/edms/delete/assignment/"+id+"/user/{#user}",
            dataType: "json",
            success: function () {
                window.location.reload();
            }
        });
    }

    function CloseAll(docId,obj) {
        $.ajax({
            url: "/ajax/edms/documentation/delete/id/"+docId,
            type: "POST",
            dataType: 'json',
            success: function (data) {
                $(obj).prop( "disabled", true );
                window.location.href = '/edms';
            }
        });
    }

    function Report(obj,docId,userId){
        $.ajax({
            url: "/ajax/edms/documentation/report/id/"+docId+"/user/"+userId,
            type: "POST",
            dataType: 'json',
            success: function (data) {
                $(obj).prop( "disabled", true );
                window.location.href = '/edms';
                if(!data['content'])
                    window.location.href = '/edms';
                else{
                    $("#rightsModal").modal('show');
                    $('#contentRight').html(data['content']);
                }
            }
        });

    }

    function Close(id,user,mode) {
        closePreview();
        $.ajax({
            url: "/ajax/edms/documentation/close/user/"+user+"/id/"+id+"/mode/"+mode,
            type: "GET",
            dataType: 'json',
            success: function (data) {
                if(!data['content'])
                    window.location.href = '/edms';
                else{
                    $("#rightsModal").modal('show');
                    $('#contentRight').html(data['content']);
                }
            }
        });
    }

    function Extension(docId,mode){
        closePreview();
        $("#rightsModal").modal('show');
        $('#contentRight').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
        var text="";
        if(mode==1)
            text="/mode/"+mode;
        $.ajax({
            url: "/ajax/edms/modal/extension/id/"+docId+text,
            type: "GET",
            dataType: 'json',
            success: function(data){
                $('#contentRight').html(data['content']);
            }
        });
    }

    function Revision(id,user,obj){
        closePreview();
        $("#rightsModal").modal('show');
        $('#contentRight').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
        $.ajax({
            url: "/ajax/edms/modal/revision/id/"+id,
            type: "POST",
            data: "user="+user,
            dataType: 'json',
            success: function (data) {
                $('#contentRight').html(data['content']);
            }
        });
    }

    function Errand(docId,userId){
        closePreview();
        $("#rightsModal").modal('show');
        $('#contentRight').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
        $.ajax({
            url: "/ajax/edms/documentation/errand/id/"+docId+"/user/"+userId,
            type: "GET",
            dataType: 'json',
            success: function(data){
                $('#contentRight').html(data['content']);
                $(this).find("button:focus" ).attr('disabled','disabled');
            }
        });
    }

    function Supplement(docId,userId) {
        closePreview();
        $("#rightsModal").modal('show');
        $('#contentRight').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
        $.ajax({
            url: "/ajax/edms/documentation/supplement/id/"+docId+"/user/"+userId,
            type: "GET",
            dataType: 'json',
            success: function(data){
                $('#contentRight').html(data['content']);
                $(this).find("button:focus" ).attr('disabled','disabled');
            }
        });
    }

    function Answer(type,docId,obj,userId){
        var text="";
        if(type==1)
            text="&date_end="+document.getElementById('date_end').value;
        $.ajax({
            url: "/ajax/edms/documentation/extension/id/"+docId+"/user/"+userId,
            type: "POST",
            data: "type="+type+text,
            dataType: 'json',
            success: function (data) {
                $(obj).prop( "disabled", true );
                window.location.href = '/edms';
            }
        });
    }

    function Sign(mode,docId,obj,userId){
        closePreview();
        $("#rightsModal").modal('show');
        $('#contentRight').html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>');
        $.ajax({
            url: "/ajax/edms/modal/signing/user/"+userId+"/id/"+docId+"/mode/"+mode,
            type: "post",
            dataType: 'json',
            success: function (data) {
                    $('#contentRight').html(data['content']);
            }
        });
    }
    function Click(page) {
        var obj=document.getElementsByClassName('act')[0];
        var id=obj.id;
        $.ajax({
            url: "/ajax/edms/documentation/?view="+id+"&user={#user}&page="+page,
            type: "POST",
            dataType: 'json',
            success: function(data){
                $("#tree").html(data['tree']);
                $("#pagination").html(data['pagination']);
            }
        });
    }

    //при любом сабмите
    $(document).on("submit", "form", function(event) {
        event.preventDefault();
        var d=new FormData(this);
        var btn = $(this).find("button[type=submit]");
        d=Check(d,btn);
        $.ajax({
            url: "/ajax/edms/documentation/"+$(btn)[0].id+"/user/{#user}",
            type: "post",
            dataType: 'json',
            data: d,
            processData: false,
            contentType: false,
            success: function (data) {
                if(d.has('edit')||d.has('rights')||d.has('suppl')){
                    if(!data['errors_none'])
                        $('#content').html(data['content']);
                    else
                        window.location.reload();
                }else{
                    if(data['tree']){
                        $('#tree').html(data['tree']);
                        document.getElementById('hide').value=0;
                        $("#hidebtn").text("Развернуть все");
                    }else
                    if(data['docs'])
                        $('#docs').html(data['docs']);
                    else{
                        if(!data['errors_none'])
                            if(d.has('ext')||d.has('ernd')||d.has('closing')||d.has('pin'))
                                $('#contentRight').html(data['content']);
                            else
                                $('#content').html(data['content']);
                        else
                            window.location.href = '/edms';
                    }

                }
                if(data['errors_none']){
                    var path = location.pathname.split('/');
                    switch (path[path.length-1]) {
                        case 'document':
                            window.location.href = '/edms/{#user}/documents';
                            break;
                        case 'assignment':
                            window.location.href = '/edms/{#user}/assignments';
                            break;
                        case 'errand':
                            window.location.href = '/edms';
                            break;
                        case 'signing':
                            window.location.href = '/edms/{#user}/documents';
                            break;
                    }
                }
                $(this).find("button:focus").removeAttr('disabled');
            }/*,
            error: function(xhr, status, error){
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage+' ' +error);
            }*/
        });
    });

    function Check(d,btn) {
        if($(btn).attr('id')=="filter"){
            var page=document.getElementsByClassName('active-page')[0];
            if(page==='undefined')
                d.append('page', 1);
            else
                d.append('page', page.id);
            d.append('type',document.getElementById('type').value);
            if(d.get('type')=="e")
                d.append('status',document.getElementsByClassName('act')[0].id);
        }else{
            btn.attr('disabled','disabled');
            if(d.has('text2')){
                d.append('text',d.get('text2'));
                d.delete('text2');
            }
            if(d.has('name2')){
                d.append('name',d.get('name2'));
                d.delete('name2');
            }
            // if(d.has('text23')){
            //     d.append('text',d.get('text2'));
            //     d.delete('text2');
            // }
            if(d.get('id_type')!=1&&d.get('id_type')!=5&&d.has('from')){
                d.delete('id_type');
                d.append('id_type',1);
            }
            /*else{
                if(document.getElementById('id_type')!=null)
                    d.append('id_type',document.getElementById('id_type').value);
            }*/
            if(document.getElementById('id_prot')!=null)
                d.append('id_prot',document.getElementById('id_prot').value);
            if(d.has('ext')){
                d.append('name',document.getElementById('name_ext').value);
                if($("#dop").length){
                    //d.append('higher',1);
                    d.append('dop',document.getElementById('dop').value);
                }
            }
            // if(d.has('closing')||d.has('rev')){
            //     d.append('name',document.getElementById('name_cl').value);
            // }
            if(d.has('edit')){
                if($("input").is("#is_tracked")&&!d.has('is_tracked'))
                    d.append('is_tracked',1);
            }
            if(d.has('ernd')){
                if(!d.has('id_user1[]')){
                    d.append('id_user',document.getElementById('id_user1').value);
                    d.delete('id_user1[]');
                }
                d.append('id_resp',document.getElementById('id_resp1').value);
                d.delete('id_resp1');
            }
        }
        return d;
    }
</script>
