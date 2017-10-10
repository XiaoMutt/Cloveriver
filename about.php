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
        <script type="text/javascript">
         $(document).ready(function (){
             $("#messenger_dialog").hide();
         });
         
        </script>
    </head>
    <body>
        <?php
            Menu();
        ?>
        <img src="css/images/about.png"/>
    </body>
</html>        