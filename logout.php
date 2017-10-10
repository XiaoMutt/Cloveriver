<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
include_once '_basics.php';
CheckUser();

?>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">

        <title>Cloveriver</title>
        <link rel="icon" href="icons/clover.ico" />        
        <style type="text/css" title="currentStyle">
            @import "css/page.css";
            @import "css/jquery-ui-1.8.16.custom.css";
        </style>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.dataTables.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="js/common.js"></script>
        <script type="text/javascript" charset="utf-8">
            function fnLogout (){
                $.post(
                "server_side_logout.php",
                
                {},
                
                function(data) {
                    if (data=="OK"){
                        window.location.href="index.php";
                    }
                    else{
                        fnMessenger("warning", "Logout failed!");
                        
                    }
                    
                },
                
                "json"
            ).error(function(){fnMessenger("error", "Server error!");});
                
            }
                
            $(document).ready(function(){
                //prepare logout_dialog;
                $('#logout_dialog').dialog({
                    autoOpen: true,
                    resizable: false,
                    height: 160,
                    modal: false,
                    open: function(event, ui) { $(this).parent().find(".ui-dialog-titlebar-close").hide(); },
                    buttons: {
                        "Yes": function() {
                            fnLogout ();
                        }
                    }
                });
                $("#messenger_dialog").hide();                
                
                
                
                
            });

        </script>
    </head>
    <body>
        <?php
            Menu();
        ?>
        <div id="logout_dialog" title="Logout Confirmation"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you really want to logout?</p></div>
    </body>
</html>        