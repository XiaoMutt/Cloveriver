<?php
    include_once("_basics.php");
    CheckUser();
    $cOpt=new OperateMyEquipment();
    echo $cOpt->AjaxSearch();
    
?>
