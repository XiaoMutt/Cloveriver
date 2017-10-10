<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 * 
 */
    include_once("_basics.php");
    CheckUser();
    $cUser=new OperateUsers();
    echo $cUser->AjaxSearch();

?>
