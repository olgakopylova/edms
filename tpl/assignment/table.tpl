<div class="row">
    <p class="h5 resp">Мои заместители</p>
</div>
<div class="content-show">
    <table id="fromMe" class="table table-striped table-bordered" style="width:100%">
        <thead>
        <tr>
            <th>ИД</th>
            <th>ФИО (Кто замещает)</th>
            <th>Даты начала</th>
            <th>Дата окончания</th>
            <th>Тип</th>
            <th>Статус</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {#table1}
        </tbody>
    </table>
</div>
<div class="row">
    <p class="h5">Мои замещения</p>
</div>
<div class="content-show">
    <table id="toMe" class="table table-striped table-bordered" style="width:100%">
        <thead>
        <tr>
            <th>ИД</th>
            <th>ФИО (Кого замещает)</th>
            <th>Даты начала</th>
            <th>Дата окончания</th>
            <th>Тип</th>
            <th>Статус</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {#table2}
        </tbody>
    </table>
</div>
<script>
    $(function(){
        initTable("#fromMe");
        initTable("#toMe");
    });
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
    function initTable(table){
        $(table).DataTable({
            pagination: true,
            language: tables_lang,
            dom: "<\"top\">rt<\"bottom\"p><\"clear\">",
            responsive: true,
            columnDefs: [
                { "visible": false, "targets": 0 }
            ],
        });
    }
</script>

