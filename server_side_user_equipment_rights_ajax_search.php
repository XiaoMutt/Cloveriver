<?php
    include_once("_basics.php");
    CheckUser();
    $cOpt=new OperateUserEquipmentRights();
    echo $cOpt->AjaxSearch();
    
?>
