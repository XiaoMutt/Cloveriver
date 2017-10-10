
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
include_once '_basics.php';
CheckUser();
$cOpt = new OperateMyEquipment();
$aaEquipment = $cOpt->getAuthEquipment();

if (count($aaEquipment)) {
    if(isset($_GET["equipment"])){
        $id=base64_decode($_GET["equipment"]);
        foreach ($aaEquipment as $aEq){
            if($id==$aEq["id"]){
                $sSelected=$id;
                break;
            }
            
        }
    }
    
    if(!isset($sSelected)){
        $sSelected=$aaEquipment[0]["id"];
    }
    
}
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
            var pickedDate;// in YYYY-mm-dd format;
            var sServerSide="server_side_equipment_record_processing.php"; //server side processing php file name;
            var sSelected=<?php echo (isset($sSelected) ? $sSelected : "false"); ?>;// the table selected;
            //
            temp=new Date();
            pickedDate=temp.getFullYear()+"-"+(temp.getMonth()+1)+"-"+temp.getDate();
            // Formating function for row details
            function fnShowDetails ( nTr )
            {
                fnMessenger("waiting", "Contacting server...");
                $.post(
                sServerSide,

                {action: "detail", iId: aData[1]}, //aData[1] contains the id of the row that was clicked;

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
                aName.push("status");
                aValue.push($('#edit_form select[name="status"]').val()); 
                aaValue.push(aValue);
                var sName=array2json(aName);
                var sValue=array2json(aaValue);
                fnMessenger("waiting", "Contacting server...");
                $.post(
                sServerSide,
                
                {action: "update", keys: sName, values: sValue},
                    
                function (data){
                    if (data["changed"]=="0"){
                        fnMessenger("warning", "Please check your input.");
                        $('#edit_message').html(data["errors"]);
                    }        
                    else{
                        if(aValue[0]==sSelected){
                            window.location.href="scheduler.php?equipment="+encode64(aData[1]);
                        }
                        else{
                            fnMessenger("OK", "Record updated.");
                            $('#edit_dialog').dialog( "close" );
                            oTable.fnClearTable();
                            oTable.fnDraw(); 
                        }
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
                
                {action: "edit", iId: aData[1]}, //aData[1] contains the id of the row that was clicked;

                function(data){
                    $('#messenger_dialog').hide();
                    for (var sVal in data){
                        $('#edit_form input[name="'+sVal+'"]').val(data[sVal]);
                    }
                    $('#edit_form select[name="status"]').val(data["status"]);                    
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
                    
                {action: "delete", sIds: sIdString},
                    
                function(data) {
                    if (data["changed"]=="0"){
                        fnMessenger("warning", "Operation failed at server side!");
                    }
                    else{
                        if(aIds[0]==sSelected){
                            window.location.href="scheduler.php";
                        }
                        else{                        
                            fnMessenger("OK", "Record deleted.");
                            oTable.fnClearTable();
                            oTable.fnDraw();   
                        }
                    }

                },
                
                "json"
            ).error(function(){fnMessenger("error", "Server error!");});
            }
            function fnDeleteReservation(div){
                var id=$(div).attr("name");
                fnMessenger("waiting", "Contacting server...");
                $.post(
                "server_side_user_equipment_reservation_record_processing.php",
                    
                {action: "delete_reservation", id: id},
                    
                function(data) {
                    if (data["changed"]=="0"){
                        fnMessenger("warning", "Operation failed at server side!");
                    }
                    else{
                        fnMessenger("OK", "Reservation deleted.");
                        $(div).remove();
                        
                    }

                },
                
                "json"
            ).error(function(){fnMessenger("error", "Server error!");});                
            }            
            
            function fnGetReservationList(index, sDate){
                var j, h1, m1, h2, m2;
                $("#revlist"+index).html("");
                fnMessenger("waiting", "Contacting server...");
                $.post(
                "server_side_user_equipment_reservation_record_processing.php",
                {action:"list", date: sDate, equipment_id: sSelected},
                function(data){
                    $('#messenger_dialog').hide();
                    for (j in data){
                        h1=Math.floor(data[j]["from"]/100);
                        m1=(data[j]["from"]%100)/100*60;
                        h2=Math.floor(data[j]["to"]/100);
                        m2=(data[j]["to"]%100)/100*60;
                        m1=(m1==0?"00":m1);
                        m2=(m2==0?"00":m2);
                        $("#revlist"+index).append('<div style="width: 100%" name="'+data[j]["id"]+'">'+h1+':'+m1+'-'+h2+':'+m2+' '+data[j]["user_id"]+data[j]["description"]+data[j]["actions"]+'</div>');
                        
                    }


                },
                "json").error(function (){fnMessenger("error", "Server error!");});        

            }
            
            function fnSetDatelist(date){
                var weekDay=['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                var indexDate=new Date();
                for (var i=1; i<6; i++){
                    $('#date'+i).html("");
                    indexDate.setFullYear(date.getFullYear(), date.getMonth(), date.getDate()-3+i);
                    $('#date'+i).html((indexDate.getMonth()+1)+"/"+indexDate.getDate()+"/"+indexDate.getFullYear()+" "+weekDay[indexDate.getDay()]);
                    fnGetReservationList(i, indexDate.getFullYear()+"-"+(indexDate.getMonth()+1)+"-"+indexDate.getDate());
                }
                
            }
            
            
            
            function fnSubmitReservation(){
                var values=$("#time_slider").slider( "option", "values" );
                var from=values[0]*100;
                var to=values[1]*100;
                var description=$("#comments").val();
                fnMessenger("waiting", "Contacting server...");
                $.post(
                "server_side_user_equipment_reservation_record_processing.php",
                {action:"reserve", date: pickedDate, equipment_id: sSelected, from: from, to: to, description: description},
                function(data){
                    if(data["changed"]==0){
                        fnMessenger("warning", data["errors"]);
                    }
                    else{
                        fnGetReservationList(3, pickedDate);
                        fnMessenger("OK", "Reservation submitted.");
                    }
                            
                },
                "json").error(function (){fnMessenger("error", "Server error!");});                 
            }
            

            
            //******document ready******

            $(document).ready(function() {
                
                
                //prepare accordion;

                $('.accordionhead').click(function() {
                    $(this).next().slideToggle(500);
                    return false;
                })

                
                //prepare datepicker;
                $( "#datepicker" ).datepicker({
                    onSelect: function (date){
                        temp=new Date(date);
                        pickedDate=temp.getFullYear()+"-"+(temp.getMonth()+1)+"-"+temp.getDate();
                        fnSetDatelist(new Date(date));
                    }
                
                });
                

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
                    "sAjaxSource": "server_side_my_equipment_ajax_search.php"
                    
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
                
                
            function fnGenerateEmailList(){
                fnMessenger("waiting", "Contacting server...");
                $.post(
                sServerSide,
                
                {action: "emaillist", id: aData[1]},
                    
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
                //prepare time_slider;
                $( "#time_slider" ).slider({
                    range: true,
                    min: 0,
                    max: 23.5,
                    step: 0.5,
                    values: [ 9, 12],
                    slide: function( event, ui ) {
                        var h1=Math.floor(ui.values[ 0 ]);
                        var m1=(ui.values[ 0 ]-h1)*60;
                        var h2=Math.floor(ui.values[ 1]);
                        var m2=(ui.values[ 1 ]-h2)*60;
                        $( "#time_slot" ).html( h1+":"+(m1==0?"00":m1)+" - "+h2+":"+(m2==0?"00":m2));
                    }
                });
                $( "#time_slot" ).html("9:00-12:00" );
              
                $('#reserve_button').button();
                $('#reserve_button').click(function(){
                    fnSubmitReservation();
                });
                
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
                
                //prepare messenger_dialog;
                $('#messenger_dialog').hide();
                
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
                    else if (this.name=="action_reserve")
                    {
                        window.location.href="scheduler.php?equipment="+encode64(aData[1]);
                    }
                    else if (this.name=="action_emaillist"){
                        fnGenerateEmailList();
                    }
                } );
                
                $('img[name="action_delete_reservation"]').live("click",
                    
                function(){
                    fnDeleteReservation($(this).parent());
                }
            );

                if(sSelected!=false){
                    fnSetDatelist(new Date()); 
                }
                               
                
                
                
            } );                
        </script>



    </head>
    <body>
        <?php
        Menu();
        ?> 
        <div id="container">
            <div class="accordion">
                <div class="ui-widget-header  ui-corner-all accordionhead">Scheduler</div>
                <div class="text ui-widget-content ui-corner-all accordionbody"> 
                    <?php
                    if (count($aaEquipment)) {
                        ?>

                        <div class="schedulerpanel1">
                            <label>1. Choose equipment</label><br/>
                            <select class="text ui-widget-content ui-corner-all" id="equipment_select" onchange="window.location.href='<?php echo $_SERVER['PHP_SELF'] . '?equipment=' ?>'+$('#equipment_select option:selected').attr('value')">
                                <?php
                                foreach ($aaEquipment as &$aEquip) {

                                    echo '<option ' . ($aEquip["id"] == $sSelected ? 'selected="selected"' : '') . ' value="' . base64_encode($aEquip["id"]) . '">' . $aEquip["name"] . '</option>';
                                }
                                ?>
                            </select>
                            <br/>
                            <div id="equipment_detail">
                                <?php
                                $cOptEquip = new OperateEquipment;
                                $aDetail = $cOptEquip->readRecord($sSelected, "brief", true);
                                foreach ($aDetail as $sKey => $sValue) {
                                    echo '<label>' . $sKey . ': </label><label name="' . $sKey . '">' . $sValue . '</label></br>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="schedulerpanel2"><div id="datepicker">2. Pick up a date</div></div>


                        <div class="schedulerpanel3">
                            <label>3. Please pick a time slot: </label>
                            <label id="time_slot"></label><div id="time_slider"></div>
                            <label><img src="icons/comment.png"/>Any thing to say?</label><br/>
                            <textarea id="comments" style="width:300px; height: 80px"></textarea><br/>
                            <button id="reserve_button" style="float:right">Add Reservation</button>
                        </div>

                        <ul id="datelist">
                            <li class="ui-state-default"><div id="date1" class="ui-widget-header ui-corner-all"></div><div id="revlist1"></div></li>
                            <li class="ui-state-default"><div id="date2" class="ui-widget-header ui-corner-all"></div><div id="revlist2"></div></li>
                            <li class="ui-state-default"><div id="date3" class="ui-widget-header ui-corner-all"></div><div id="revlist3"></div></li>
                            <li class="ui-state-default"><div id="date4" class="ui-widget-header ui-corner-all"></div><div id="revlist4"></div></li>
                            <li class="ui-state-default"><div id="date5" class="ui-widget-header ui-corner-all"></div><div id="revlist5"></div></li>
                        </ul>
                        <?php
                    } else {
                        ?>
                        You do not have access to any equipment.<br/>
                        <?php
                    }
                    ?>

                </div>
            </div>
            <div class="spacer"></div>
            <div class="accordion">
                <div class="ui-widget-header ui-corner-all accordionhead">Equipment that I can use</div>
                <div class="table_jui">
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
        </div>
        
        <!--email list dialog-->
        <div id="emaillist" title="Please copy the follow email list to your email client">
            <textarea id="emaillist_textarea" style="width:360px; height: 180px;"></textarea>
        </div>


        <!--delete dialog-->
        <div id="delete_confirmation_dialog" title="Delete confirmation"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you really want to delete this equipment?</p></div>

        <!--edit dialog-->
        <div id="edit_dialog" title="Edit this equipment">
            <form id="edit_form">
                <div id="edit_message">Please Enter</div>
                <fieldset>
                    <?php
                    $cOpt->getFields($aFieldNames, "name", "edit");
                    $cOpt->getFields($aFieldLabels, "label", "edit");
                    for ($i = 0; $i < count($aFieldNames); $i++) {
                        if ($aFieldNames[$i] == "status") {
                            echo '<label>' . $aFieldLabels[$i] . '</label>';
                            echo '<select name="' . $aFieldNames[$i] . '" class="text ui-widget-content ui-corner-all">
                                        <option value="normal">normal</option>
                                        <option value="problematic">problematic</option>
                                        <option value="broken">broken</admin>
                                  </select>
                                  <br/>
                                  ';
                        } else {
                            echo '<label>' . $aFieldLabels[$i] . '</label>';
                            echo '<input type="text" name="' . $aFieldNames[$i] . '" class="text ui-widget-content ui-corner-all" /><br/>';
                        }
                    }
                    ?>
                </fieldset>
            </form>            
        </div>
    </body>
</html>