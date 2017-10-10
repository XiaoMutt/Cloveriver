<?php

/*
 * This file handle the post request send by ajax.
 * Input data: 
 * "action" should be "add" "edit" "delete";
 * "table" should be the desired table.
 * "id" should be the record id;
 * 
 * 
 */
include_once('_basics.php');
CheckUser();

$cOpt = new OperateEquipment();

function checkInput($sKey, $sValue) {
    global $cOpt;
    $sMsg = "";
    $sValue = trim($sValue);
    if ($sKey == "name") {
        if ($sValue == "") {
            $sMsg.="Please enter an equipment name.<br/>";
        } else {
            $aRecord = Array("name" => $sValue);
            if ($cOpt->checkIfRecordExist($aRecord)) {
                $sMsg.="Equipment name entered was already in use.<br/>";
                $sMsg.="Please choose another name.<br/>";
            }
        }
    } elseif ($sKey == "location" && $sValue == "") {
        $sMsg.="Please enter the location of the equipment.<br/>";
    }
    return $sMsg;
}

if ($_POST["action"] == "detail") {
    $jResult = $cOpt->AjaxRead($_POST['iId'], "detail", true);
} else if ($_POST["action"] == "update") {
    $aaValues = json_decode($_POST["values"], true);
    $aKeys = json_decode($_POST['keys'], true);

    for ($i = 0; $i < count($aaValues); $i++) {
        for ($j = 0; $j < count($aKeys); $j++) {
            $aaData[$i][$aKeys[$j]] = $aaValues[$i][$j];
        }
    }
    $jResult = $cOpt->AjaxUpdate($aaData);
} else if ($_POST["action"] == "add") {
    $aaValues = json_decode($_POST["values"], true);
    $aKeys = json_decode($_POST['keys'], true);
    $sErrors = "";
    for ($i = 0; $i < count($aaValues); $i++) {
        for ($j = 0; $j < count($aKeys); $j++) {
            $sErrors.=checkInput($aKeys[$j], $aaValues[$i][$j]);
            $aaData[$i][$aKeys[$j]] = $aaValues[$i][$j];
        }
    }
    if ($sErrors == "") {
        $jResult = $cOpt->AjaxAdd($aaData);
    } else {
        $aErrors = Array("changed" => 0, "errors" => $sErrors);
        $jResult = json_encode($aErrors);
    }
} else if ($_POST["action"] == "edit") {
    $jResult = $cOpt->AjaxRead($_POST['iId'], "edit");
} else if ($_POST["action"] == "delete") {
    $aIds = explode(",", $_POST["sIds"]);
    $jResult = $cOpt->AjaxDelete($aIds);
} else if ($_POST["action"] == "apply") {
    $aIds = explode(",", $_POST["sIds"]);
    for ($i = 0; $i < count($aIds); $i++) {
        $aaValues[$i]['user_id'] = $cOpt->iSessionUserId;
        $aaValues[$i]['position'] = "applicant";
        $aaValues[$i]['equipment_id'] = $aIds[$i];
    }
    $cOptUER = new OperateUserEquipmentRights();
    $jResult = $cOptUER->addRecords($aaValues);
}else if ($_POST["action"]=="emaillist"){
    $jResult=$cOpt->AjaxGenerateEquipmentUserGroupEmailList($_POST["id"]);
}


echo $jResult;
?>
