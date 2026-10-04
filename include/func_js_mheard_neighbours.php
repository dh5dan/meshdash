<script>
   $(function ($) {

        $("#btnGetMheardNeighbours").on("click", function ()
        {
            let sendData  = 1;

            $("#pageLoading").show();
            $("#sendData").val(sendData);
            $("#frmMheardNeighbours").trigger('submit');
        });
   });

</script>