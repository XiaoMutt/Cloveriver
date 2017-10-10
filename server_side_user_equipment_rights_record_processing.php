<?php

include_once('_basics.php');
CheckUser();

$cOpt = new OperateUserEquipmentRights();
$sErrors = "";

if (!$cOpt->sSessionUserType == "admin") {
    foreach ($aIds as $iId) {
        $aRecord['user_id'] = $cOpt->iSessionUserId;
        $aRecord['equipment_id'] = $iId;
        $aRecord['position'] = 'manager';
        if (!$cOpt->checkIfRecordExist($aRecord)) {
            $sErrors.="You do not have rights to perform this operation.<br/>";
            break;
        }
    }
}

if ($sErrors == "") {
    if ($_POST['action'] == 'update') {
        $aaValues = json_decode($_POST["values"], true);
        $aKeys = json_decode($_POST['keys'], true);

        for ($i = 0; $i < count($aaValues); $i++) {
            for ($j = 0; $j < count($aKeys); $j++) {
                $aaData[$i][$aKeys[$j]] = $aaValues[$i][$j];
            }
        }
        $jResult = $cOpt->AjaxUpdate($aaData);
    } elseif ($_POST['action'] == 'delete') {
        $aIds = explode(",", $_POST["sIds"]);
        $jResult = $cOpt->deleteRecords($aIds);
    } elseif ($_POST["action"] == 'edit') {
        $jResult = $cOpt->AjaxRead($_POST['iId'], "edit");
    }
}else{
    $aErrors=Array("changed"=>0, "errors"=>$sErrors);
    $jResult=json_encode($aErrors);
}

echo $jResult;


?>
