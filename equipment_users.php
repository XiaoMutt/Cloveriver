
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
            var sServerSide="server_side_user_equipment_rights_record_processing.php"; //server side processing php file name;
            var sSelected="";// the table selected;
            
            //function for send edited form to server;
            function fnUpdateRecord(){
                var aName =['id'];
                var aValue=new Array();
                var aaValue=new Array();
                aValue.push(aData[1]);
                aName.push("position");
                aValue.push($('#edit_form select[name="position"]').val()); 
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
                        fnMessenger("OK", "User Position changed.");
                        $('#edit_dialog').dialog( "close" );
                        oTable.fnClearTable();
                        oTable.fnDraw();                              
                    }
                        
                },
                    
                "json"
            ).error(function (){fnMessenger("error", "Server error!");});
                
            }

            function fnFormatChangePositionDialog(){
                $('#edit_message').html("Please Enter");
                fnMessenger("waiting", "Contacting server...");
                
                $.post(
                sServerSide,
                
                {action: "edit", table:sSelected, iId: aData[1]}, //aData[1] contains the id of the row that was clicked;

                function(data){
                    $('#messenger_dialog').hide();
                    $('#edit_form select[name="position"]').val(data["position"]);                    
                    $("#edit_dialog").dialog("open");
                },

                "json"                
            ).error(function(){fnMessenger("error", "Server error!");});
            
               
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
                        fnMessenger("OK", "User removed from the user group of the equipment.");
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
                    "bProcessing": true,
                    "bJQueryUI": true,
                    "bServerSide": true,
                    "sAjaxSource": "server_side_user_equipment_rights_ajax_search.php"
                    
                } );

                //prepare edit_dialog;
                $('#edit_dialog').dialog({
                    autoOpen: false,
                    width: 400,
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
               
                //prepare delete_confirmation_dialog;
                $('#delete_confirmation_dialog').dialog({
                    autoOpen: false,
                    resizable: false,
                    height: 200,
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
                
                $('#data_table tbody td img').live( 'click', function () {
                    var nTr = this.parentNode.parentNode;
                    aData = oTable.fnGetData( nTr ); //get the data from that row; aData[1] contains the id of that record;
                     
                    if (this.name=="action_change_position")
                    {
                        fnFormatChangePositionDialog();//fill the edit_dialog with existed data;
                    }

                    else if (this.name=="action_delete")//delete icon;
                    {
                        $("#delete_confirmation_dialog").dialog("open");
                    }
                } );
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
                <label>Equipment and User Rights</label>

                <!--Add button-->               
                <?php
                $cOpt = new OperateUserEquipmentRights();
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
        <div id="delete_confirmation_dialog" title="Remove confirmation"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you really want to remove this user from the user group of this equipment?</p></div>

        <!--edit dialog-->
        <div id="edit_dialog" title="Edit this equipment">
            <form id="edit_form">
                <div id="edit_message">Please Enter</div>
                <fieldset>
                    <?php
                    echo '<label>Change the user Position to</label>';
                    echo '<select name="position" class="text ui-widget-content ui-corner-all">
                                <option value="applicant">applicant</option>
                                <option value="member">member</option>
                                <option value="manager">manager</admin>
                          </select>
                          <br/>
                          ';
                    ?>
                </fieldset>
            </form>            
        </div>
    </body>
</html>