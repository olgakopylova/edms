<div class="content-show" id="docs">
    <table id="documents" class="table table-striped table-bordered" style="width: 100%;">
        <thead>
        <tr>
            <th>ИД</th>
            <th>Наименование</th>
            <th>Содержание</th>
            <th>Дата</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {#table}
        </tbody>
    </table>
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

    $('#documents').DataTable({
        pagination: true,
        language: tables_lang,
        dom: "<\"top\">rt<\"bottom\"p><\"clear\">",
        responsive: true,
        columnDefs: [
            { "visible": false, "targets": 0 }
        ],
        order:[[ 3, "desc" ]],
    });

</script>