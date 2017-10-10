
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
            function fnLogin(){
                var sEmail=$('#login_email').val();
                var sPassword=Sha1.hash($('#login_password').val());
                $.post(
                    "server_side_user_login.php",

                    {email: sEmail, password: sPassword},

                    function(msg){
                        if(msg=="OK"){
                            window.location.href="scheduler.php";
                        }
                        else{
                            $('#login_message').text(msg);
                        }

                    },

                    "json"
                ).error(function (){alert("Login failed due to server connection error!")});                
            }
            
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
                "server_side_user_record_processing.php",
                    
                {action: "add", keys: sName, values: sValue},
                    
                function (data){
                    if (data["changed"]==0){
                        $('#register_message').html(data["errors"]);
                    }        
                    else{
                        $('#register_dialog').dialog("close");
                    }
                        
                },
                    
                "json"
            ).error(function (){alert("Register failed due to server connection error!")});                
                
            }
            
            function fnFormatRegisterDialog(){
                $('#register_message').html("Please Enter");
                $('#register_form input').val("");
                $('#register_dialog').dialog("open");
            }
            
            $('document').ready(function(){
                $('#login_dialog').dialog({
                    autoOpen: true,
                    width: 300,
                    modal: false,
                    open: function(event, ui) { $(this).parent().find(".ui-dialog-titlebar-close").hide(); },
                    buttons: {
                        "OK": function() {
                            fnLogin();
                        },
                        "New User": function() {
                            fnFormatRegisterDialog();
                        }
                    }
                });
                
                $('#register_dialog').dialog({
                    autoOpen: false,
                    width: 300,
                    modal: true,
                    buttons: {
                        "OK": function() {
                            fnRegister();
                        },
                        "Cancel": function() {
                            $( this ).dialog( "close" );
                        }
                    }
                }); 
              
            }
           
            
);
        </script>
    </head>
    <body>
        <div id="login_dialog" title="Cloveriver Login">
            <label id="login_message">Please Enter</label>
            <form id="login_form">
                <fieldset>
                    <label>Email</label><br/><input type="text" autocomplete="on" name="Email" id="login_email" class="text ui-widget-content ui-corner-all"/><br/>
                    <label>Password</label><br/><input type="password" name="Password" id="login_password" class="text ui-widget-content ui-corner-all"/><br/>
                </fieldset>
            </form>            
        </div> 
        <div id="register_dialog" title="Register New User">
            <div id="register_message">Please Enter</div>
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
