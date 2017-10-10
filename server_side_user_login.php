<?php
    include_once('_basics.php');
    $cUser=new OperateUsers();
    $sResult=$cUser->Login($_POST["email"], $_POST["password"]);
    echo json_encode($sResult);
?>
