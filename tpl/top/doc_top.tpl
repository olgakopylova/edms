<div class="row">
    <div class="col-md-2"><p class="h5">{#fio}</p></div>
    <div class="col-md-10"><p class="h5">Документы</p></div>
</div>
<div class="row">
    <a class="btn btn-sm btn-outline-success float-right btn-top" href="/edms/{#user}/create/document" target="_blank" role="button"> Создать документ </a>
    <a class="btn btn-sm btn-outline-primary float-right btn-top" href="/edms" role="button"> Поручения </a>
    <a class="btn btn-sm btn-outline-primary float-right btn-top" href="/edms/{#user}/documents" role="button"> Документы </a>
    <a class="btn btn-sm btn-outline-primary float-right btn-top" href="/edms/{#user}/assignments" role="button"> Заместители </a>

</div>
<div class="row">
    <a data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">Фильтр</a>
    <!--<button class="btn btn-sm btn-outline-secondary filter " type="button" data-toggle="collapse" data-target="#collapseExample" aria-controls="collapseExample">Фильтр</button>-->
</div>
<div class="collapse" id="collapseExample">
    <div class="card card-body">
        <form  method="post" class="needs-validation" autocomplete="on">
            <div class="col-md-12">
                <div class="row">
                    <input id="type" value="d" hidden>
                    <div class="col-md-4">
                        <label for="date_start" class="control-label">С</label>
                        <input type="date" id="date_start" name="date_start" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="date_end" class="control-label">По</label>
                        <input type="date" id="date_end" name="date_end" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="id_type" class="control-label">Тип</label>
                        <select name="id_type" data-placeholder="Выберите ответственного" id="id_type" class="selectpicker form-control"  {if not_mult}disabled{/if}>{#rows1}</select>
                    </div>
                </div>
                <button type="submit" id="filter" class="btn btn-sm btn-outline-primary float-right btn-filter">Применить</button>
            </div>
        </form>
    </div>
</div>
