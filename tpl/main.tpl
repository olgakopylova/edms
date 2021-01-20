<div class="main_cont">
    <div class="col">
        {#top}
    </div>
    <div class="form-group content" id="tree">
        {#content}
    </div>
</div>

<script>

    function Hide() {
        var mode=document.getElementById('hide').value;
        if(mode==1) {
            document.getElementById('hide').value=0;
            $("#hidebtn").text("Развернуть все");
            $('i').each(function(i,elem) {
                elem.classList.add("fa-plus");
                elem.classList.remove("fa-minus");
            });
            $('details').each(function(i,elem) {
                $(elem).prop( "open", false );
            });
        }
        else{
            document.getElementById('hide').value=1;
            $("#hidebtn").text("Свернуть все");
            $('i').each(function(i,elem) {
                elem.classList.add("fa-minus");
                elem.classList.remove("fa-plus");
            });
            $('details').each(function(i,elem) {
                $(elem).prop( "open", true );
            });
        }
    }
</script>


