<script>       
    <?php if($notification){ ?>        
        ativarNotification();        
    <?php } ?>       
    
    function ativarNotification(){
        document.getElementById("noti").style.display = 'block';
        setTimeout(function() {
            document.getElementById("noti").style.display = 'none';
        }, 7000); 
    }
</script>