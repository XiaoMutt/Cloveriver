
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
include_once '_basics.php';
?>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />

        <title>Cloveriver</title>
        <link rel="icon" href="icons/clover.ico" />
        <style type="text/css" title="currentStyle">
            @import "css/jquery-ui-1.8.16.custom.css";
            @import "css/page.css";
        </style>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="js/common.js"></script>
        <script type="text/javascript" charset="utf-8">
            
            function fnRegister(){
                var aName =new Array();
                var aValue=new Array();
                var aaValue=new Array();
                
                $('#register_form input').each(function(){
                    aName.push(this.id);
                    if (this.id=="password"||this.id=="repeat_password"){
                        aValue.push(Sha1.hash($(this).val()));
                    }
                    else{
                        aValue.push($(this).val());
                    }
                });
                aaValue.push(aValue);
                var sName=array2json(aName);
                var sValue=array2json(aaValue);
                $.post(
                "server_side_setup_processing.php",
                    
                {keys: sName, values: sValue},
                    
                function (data){
                    if (data["changed"]==0){
                        $('#register_message').html(data["errors"]);
                    }        
                    else{
                        window.location.href="scheduler.php";
                    }
                        
                },
                    
                "json"
            ).error(function (){alert("Setup failed due to server error!")});                
                
            }
            
            function fnFormatRegisterDialog(){
                $('#register_message').html("Please Enter");
                $('#register_form input').val("");
                $('#register_dialog').dialog("open");
            }
            
            $('document').ready(function(){
                $('#register_dialog').dialog({
                    autoOpen: true,
                    width: 300,
                    modal: false,
                    open: function(event, ui) { $(this).parent().find(".ui-dialog-titlebar-close").hide(); },
                    buttons: {
                        "OK": function() {
                            fnRegister();
                        }
                    }
                }); 
              
            }
           
            
        );
        </script>
    </head>
    <body>
        <?php
        $whitelist = array('localhost', '127.0.0.1');

        if (!in_array($_SERVER['HTTP_HOST'], $whitelist)) {
            echo 'Forbidden! Please visit this page using "localhost".';
        }
        ?>
        <div id="register_dialog" title="Clover Setup">
            <div id="register_message">Create an Administrator Account</div>
            <form id="register_form" >
                <fieldset>
                    <label>Name</label><br/><input type="text" name="Name" id="name" class="text ui-widget-content ui-corner-all"/><br/>
                    <label>Email</label><br/><input type="text" name="Email" id="email" class="text ui-widget-content ui-corner-all"/><br/>
                    <label>Password</label><br/><input type="password" name="Password" id="password" class="text ui-widget-content ui-corner-all"/><br/>
                    <label>Repeat Password</label><br/><input type="password" name="Repeat Password" id="repeat_password" class="text ui-widget-content ui-corner-all"/><br/>
                    <label>Description</label><br/><input type="text" name="Description" id="description" class="text ui-widget-content ui-corner-all"/><br/>
                </fieldset>
            </form>            
        </div>            
    </body>
    <?php
    /*
     * To change this template, choose Tools | Templates
     * and open the template in the editor.
     */
    ?>
