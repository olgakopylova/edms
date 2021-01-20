<div class='row input-group mb-3' onselectstart='return false' onmousedown='return false'>
    <div class='col-lg-12' id='main' {#option}>
    <div class='row'>
        <div class='col-1' id='node' onclick='Open(this)'><button type='button' id='node_btn' class='btn'>{#collapse}</button></div>
        <div class='col-7 col-sm-9 col-md-6 col-lg-7 text_branch' title='Предпросмотр' id='{#docId}' style='{#style}' onclick='Preview(this)'>
        <div class='inner-text'>{#name}<br>
            {#more}</div>
    </div>
    <div class='col-md-3 col-lg-2 reset status' id='{#docId}' onclick='Preview(this)' title='Статус'>
    <div class='inner-text'>{#status}</div>
</div>
<div class='col-2 col-sm-1 reset' title='Просмотреть полностью'>{#bookmark}</div>
<div class='col-2 col-sm-1 reset' title='Просмотреть полностью'>
    <button type='button' id='more' onclick="event.stopPropagation();window.location.href ='/edms/{#userId}/errand/{#docId}';" class='btn' aria-hidden="true"><i  class="fa fa-ellipsis-h" ></i></button></div>
</div>
</div>
<details open {#hide}><summary></summary>
