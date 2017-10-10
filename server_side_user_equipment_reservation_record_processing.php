<?php
include_once ('_basics.php');

CheckUser();
$cOpt=new OperateUserEquipmentReservations();

if($_POST["action"]=="reserve"){
    $aRev=Array(
        "user_id"=>$cOpt->iSessionUserId,
        "equipment_id"=>$_POST["equipment_id"],
        "date"=>$_POST["date"],
        "from"=>$_POST["from"],
        "to"=>$_POST["to"],
        "description"=>$_POST["description"]
        );
    if ($cOpt->checkTimeConflict($aRev["equipment_id"], $aRev["date"], $aRev["from"], $aRev["to"])){
        $aaData=Array(
            "changed"=>0,
            "errors"=>"The time slot you chose conflicts with others. Please reselect."
        );
        $jResult=json_encode($aaData);
        
    }
    else{
        $aaData=Array($aRev);
        $jResult=$cOpt->AjaxAdd($aaData);
    }
    
    
}
elseif($_POST["action"]=="list") {
    $jResult=$cOpt->AjaxGetReservation($_POST["date"], $_POST["equipment_id"]);
}
elseif($_POST["action"]=="delete_reservation"){
    $aIds=Array($_POST["id"]);
    $jResult=$cOpt->deleteRecords($aIds);
}


echo $jResult;

?>
