<?php
    
    include_once('_basics.php');
    CheckUser();
    $cOpt=new OperateEquipment();
    echo $cOpt->AjaxSearch();
?>
