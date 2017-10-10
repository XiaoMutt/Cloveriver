
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
include_once '_basics.php';
CheckUser();
?>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />

        <title>Cloveriver</title>
        <link rel="icon" href="icons/clover.ico" />
        <style type="text/css" title="currentStyle">
            @import "css/page.css";
            @import "css/data_table_jui.css";
            @import "css/jquery-ui-1.8.16.custom.css";
        </style>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.dataTables.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="js/common.js"></script>
        <script type="text/javascript" charset="utf-8">
            var oTable; //datatable object;
            var aData=Array(); //Data;
            var sServerSide="server_side_user_record_processing.php"; //server side processing php file name;
            var sSelected="";// the table selected;
            
            // Formating function for row details
            function fnShowDetails ( nTr )
            {
                fnMessenger("waiting", "Contacting server...");
                $.post(
                sServerSide,

                {action: "detail", table:sSelected, iId: aData[1]}, //aData[1] contains the id of the row that was clicked;

                function(data){
                    $('#messenger_dialog').hide();
                    var sOut='<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">';
                    for (var sVal in data){
                        sOut+="<tr><td>"+sVal+": </td><td>"+data[sVal]+"</td></tr>";
                    }
                    sOut += '</table>';

                    oTable.fnOpen( nTr, sOut, 'details' );
                },

                "json"
            ).error(function (){fnMessenger("error", "Server error!");});
            }
            
            //function for add records;
            function fnRegister(){
                var aName =new Array();
                var aValue=new Array();
                var aaValue=new Array();
                
                $('#add_form input').each(function(){
                    aName.push(this.name);
                    if (this.name=="password"||this.name=="repeat_password"){
                        aValue.push(Sha1.hash($(this).val()));
                    }
                    else{
                        aValue.push($(this).val());
                    }
                });
                aName.push("identity");
                aValue.push($('#add_form select[name="identity"]').val());                
                aaValue.push(aValue);
                var sName=array2json(aName);
                var sValue=array2json(aaValue);
                $.post(
                sServerSide,
                    
                {action: "add", keys: sName, values: sValue},
                    
                function (data){
                    if (data["changed"]==0){
                        fnMessenger("warning", "Please check your input.");
                        $('#add_message').html(data["errors"]);
                    }        
                    else{
                        fnMessenger("OK", "New user added.")
                        $('#add_dialog').dialog("close");
                        oTable.fnClearTable();
                        oTable.fnDraw();                           
                    }
                        
                },
                    
                "json"
            ).error(function (){fnMessenger("error", "Server error!");});                
                
            }
            
            function fnChangePassword (){
                var aName =['id'];
                var aValue=new Array();
                aValue.push(aData[1]);
                $('#change_password_dialog input').each(function(){
                    aName.push($(this).attr('name'));
                    aValue.push(Sha1.hash($(this).val()));
                });
                var sName=array2json(aName);
                var sValue=array2json(aValue);                
                fnMessenger("waiting", "Contacting server...");
                $.post(
                sServerSide,
                
                {action: "change_password", table:sSelected, keys: sName, values: sValue},
                    
                function (data){
                    if (data["changed"]=="0"){
                        fnMessenger("warning", "Please check your input.");
                        $('#change_password_dialog_message').html(data["errors"]);
                    }        
                    else{
                        fnMessenger("OK", "Password changed.");
                        $('#change_password_dialog').dialog( "close" );
                        oTable.fnClearTable();
                        oTable.fnDraw();                              
                    }
                        
                },
                    
                "json"
            ).error(function (){fnMessenger("error", "Server error!");});                
            }
                
          
            //function for send edited form to server;
            function fnUpdateRecord(){
                var aName =['id'];
                var aValue=new Array();
                var aaValue=new Array();
                aValue.push(aData[1]);
                $('#edit_form input').each(function(){
                    aName.push($(this).attr('name'));
                    aValue.push($(this).val());
                });
                if($('#edit_form select[name="identity"]').val()){
                    aName.push("identity");
                    aValue.push($('#edit_form select[name="identity"]').val());                    
                }

                aaValue.push(aValue);
                var sName=array2json(aName);
                var sValue=array2json(aaValue);
                fnMessenger("waiting", "Contacting server...");
                $.post(
                sServerSide,
                
                {action: "update", table:sSelected, keys: sName, values: sValue},
                    
                function (data){
                    if (data["changed"]=="0"){
                        fnMessenger("warning", "Please check your input.");
                        $('#edit_message').html(data["errors"]);
                    }        
                    else{
                        fnMessenger("OK", "Record updated.");
                        $('#edit_dialog').dialog( "close" );
                        oTable.fnClearTable();
                        oTable.fnDraw();                              
                    }
                        
                },
                    
                "json"
            ).error(function (){fnMessenger("error", "Server error!");});
                
            }
            
            function fnGenerateEmailList(){
                fnMessenger("waiting", "Contacting server...");
                $.post(
                sServerSide,
                
                {action: "emaillist"},
                    
                function (data){
                    if (data["changed"]=="0"){
                        fnMessenger("warning", "Server side error.");
                    }        
                    else{
                        fnMessenger("OK", "Email list generated.");
                        $('#emaillist_textarea').val(data["emaillist"]);
                        $('#emaillist').dialog( "open" );
                    }
                        
                },
                    
                "json"
            ).error(function (){fnMessenger("error", "Server error!");});
            }            

            function fnFormatEditDialog(){
                $('#edit_message').html("Please Enter");
                fnMessenger("waiting", "Contacting server...");
                
                $.post(
                sServerSide,
                
                {action: "edit", table:sSelected, iId: aData[1]}, //aData[1] contains the id of the row that was clicked;

                function(data){
                    $('#messenger_dialog').hide();
                    for (var sVal in data){
                        $('#edit_form input[name="'+sVal+'"]').val(data[sVal]);
                    }
                    $('#edit_form select[name="identity"]').val(data["identity"]);
                    $("#edit_dialog").dialog("open");
                },

                "json"                
            ).error(function(){fnMessenger("error", "Server error!");});
            
               
            }
            
            function fnFormatAddDialog(){
                $('#add_message').html("Please Enter");
                $("#add_form input").val("");
                $("#add_dialog").dialog("open");
            }
            
            function fnFormatChangePasswordDialog(){
                $('#change_password_dialog_message').html("Please Enter");
                $('#change_password_dialog input').val("");
                $("#change_password_dialog").dialog("open");
            }
            
            
            
            //function for deleting records;
            function fnDeleteRecords ()
            {
                //make the clicked row id into a array and then in a string in which ids are separated by ",". This is because the deleteRecords functions require an array as argument;
                var aIds=Array();
                aIds[0]=aData[1];
                sIdString=aIds.join(",");
                               
                fnMessenger("waiting", "Contacting server...");
                $.post(
                sServerSide,
                    
                {action: "delete", table:sSelected, sIds: sIdString},
                    
                function(data) {
                    if (data["changed"]=="0"){
                        fnMessenger("warning", "Operation failed at server side!");
                    }
                    else{
                        fnMessenger("OK", "Record deleted.");
                        oTable.fnClearTable();
                        oTable.fnDraw();                        
                    }

                },
                
                "json"
            ).error(function(){fnMessenger("error", "Server error!");});
            }
            
            
            //******document ready******

            $(document).ready(function() {

                //prepare datatable;
                oTable = $('#data_table').dataTable( {
                    "aoColumnDefs": [
                        { "bSortable": false, "aTargets": [ 0 ] }
                    ],
                    "aaSorting": [[1, 'asc']]     ,
                    "sPaginationType": "full_numbers",
                    "bServerSide": true,
                    "bProcessing": true,
                    "bJQueryUI": true,
                    "sAjaxSource": "server_side_user_ajax_search.php"
                    
                } );

                //prepare edit_dialog;
                $('#edit_dialog').dialog({
                    autoOpen: false,
                    width: 600,
                    modal: true,
                    buttons: {
                        "OK": function() {
                            fnUpdateRecord ();
                        },
                        "Cancel": function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
                //prepare change_password_dialog;
                $('#change_password_dialog').dialog({
                    autoOpen: false,
                    width: 400,
                    modal: true,
                    buttons: {
                        "OK": function() {
                            fnChangePassword ();
                        },
                        "Cancel": function() {
                            $( this ).dialog( "close" );
                        }
                    }                
                
                
                })
               
                //prepare add_dialog;
                $('#add_dialog').dialog({
                    autoOpen: false,
                    width: 600,
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
                
                
                //prepare delete_confirmation_dialog;
                $('#delete_confirmation_dialog').dialog({
                    autoOpen: false,
                    resizable: false,
                    height: 160,
                    modal: true,
                    buttons: {
                        "Delete": function() {
                            fnDeleteRecords ();
                            $( this ).dialog( "close" );
                        },
                        "Cancel": function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
                
                //prepare messenger_dialog;
                $('#messenger_dialog').hide();
                
                //prepare emaillist dialog;
                $('#emaillist').dialog({
                    autoOpen: false,
                    resizable: true,
                    height: 300,
                    width: 400,
                    modal: true,
                    buttons: {
                        "Got it": function() {
                            $( this ).dialog( "close" );
                        }
                    }                
                });                
                
                $('#data_table tbody td img').live( 'click', function () {
                    var nTr = this.parentNode.parentNode;
                    aData = oTable.fnGetData( nTr ); //get the data from that row; aData[1] contains the id of that record;
                     
                    if (this.name=="action_open")//detail icon close;
                    {
                        // This row is already open - close it
                        this.name="action_close";
                        oTable.fnClose( nTr );
                    }
                    else if(this.name=="action_close")//detail icon open;
                    {
                        // Open this row
                        this.name="action_open";
                        fnMessenger("waiting", "Retrieving data from server...");
                        fnShowDetails(nTr);
                    }
                    else if (this.name=="action_edit")
                    {
                        fnFormatEditDialog();//fill the edit_dialog with existed data;
                    }

                    else if (this.name=="action_delete")//delete icon;
                    {
                        $("#delete_confirmation_dialog").dialog("open");
                    }
                    else if (this.name=="action_password")
                    {
                        fnFormatChangePasswordDialog();
                    }
                } );
                
                
                $('#add').live ('click', function (){
                    fnFormatAddDialog();//clean the edit_dialog and show;
                });
                
                $('#generateemaillist').live ('click', function (){
                    fnGenerateEmailList();//clean the edit_dialog and show;
                });                
                
            } );                
        </script>
    </head>
    <body>
        <?php
            Menu();
        ?>        
        <div id="container">
            <div class="table_jui">

                <!--Database Label-->
                <label>Roster</label>

                <!--Add button-->               
                <?php
                    $cOpt = new OperateUsers();
                    if ($cOpt->sSessionUserType=="admin"){
                        echo '<img src="icons/add.png" id="add"/>';
                        echo '<img src="icons/email.png" id="generateemaillist" title="Generate email list of the users in the Cloveriver"/>';
                    }
                ?>
                

                <br/>
                
                <!--Data Table-->
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="data_table">
                    <thead>
                        <tr>
                            <?php
                            $aFields = array();

                            $cOpt->getFields($aFields, "label", "brief");
                            echo "<th>Actions</th>";
                            foreach ($aFields as &$sColumn) {
                                echo "<th>" . $sColumn . "</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <!--delete dialog-->
        <div id="delete_confirmation_dialog" title="Delete Confirmation"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you really want to delete this record?</p></div>

        <!--add dialog-->
        <div id="add_dialog" title="Add a user">
            <form id="add_form">
                <div id="add_message">Please Enter</div>
                <fieldset>
                    <?php
                    $cOpt->getFields($aFieldNames, "name", "edit");
                    $cOpt->getFields($aFieldLabels, "label", "edit");                    
                    for ($i = 0; $i < count($aFieldNames); $i++) {
                        if($aFieldNames[$i]=="identity"){
                            echo '<label>' . $aFieldLabels[$i] . '</label>';
                            echo '<select name="' . $aFieldNames[$i] . '" class="text ui-widget-content ui-corner-all">
                                        <option value="visitor">visitor</option>
                                        <option value="user">user</option>
                                        <option value="admin">admin</admin>
                                  </select>
                                  <br/>
                                  ';
                            
                        }
                        elseif($aFieldNames[$i]=="password"){
                            echo '<label>Password</label>';
                            echo '<input type="password" name="password" class="text ui-widget-content ui-corner-all" /><br/>';
                            echo '<label>Repeat Password</label>';
                            echo '<input type="password" name="repeat_password" class="text ui-widget-content ui-corner-all" /><br/>';
                        }
                        else{
                            echo '<label>' . $aFieldLabels[$i] . '</label>';
                            echo '<input type="text" name="' . $aFieldNames[$i] . '" class="text ui-widget-content ui-corner-all" /><br/>';
                            
                        }

                    }
                    ?>
                </fieldset>
            </form>            
        </div>

        <!--edit dialog-->
        <div id="edit_dialog" title="Edit this user">
            <form id="edit_form">
                <div id="edit_message">Please Enter</div>
                <fieldset>
                    <?php
                    for ($i = 0; $i < count($aFieldNames); $i++) {
                        if($aFieldNames[$i]=="identity"){
                            if($cOpt->sSessionUserType=="admin"){
                                echo '<label>' . $aFieldLabels[$i] . '</label>';
                                echo '<select name="' . $aFieldNames[$i] . '" class="text ui-widget-content ui-corner-all">
                                            <option value="visitor">visitor</option>
                                            <option value="user">user</option>
                                            <option value="admin">admin</admin>
                                      </select>
                                      <br/>
                                      ';
                            }
                            
                        }
                        elseif($aFieldNames[$i]!="password"){
                            echo '<label>' . $aFieldLabels[$i] . '</label>';
                            echo '<input type="text" name="' . $aFieldNames[$i] . '" class="text ui-widget-content ui-corner-all" /><br/>';
                        }

                    }
                    ?>
                </fieldset>
            </form>            
        </div>
        
        <!--email list dialog-->
        <div id="emaillist" title="Please copy the follow email list to your email client">
            <textarea id="emaillist_textarea" style="width:360px; height: 180px;"></textarea>
        </div>        
        
        <div id="change_password_dialog" title="Change Password">
            <form id="change_password_form">
                <div id="change_password_dialog_message">Please Enter</div>
                <fieldset>
                    <label>Old Password</label><input type="password" name="old_password"/><br/>
                    <label>New Password</label><input type="password" name="password"/><br/>
                    <label>Repeat New Password</label><input type="password" name="repeat_password"/><br/>                    
                </fieldset>    
                
            </form>
        </div>
    </body>
</html>