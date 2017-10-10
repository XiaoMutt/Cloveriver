<?php

/*
 * Create database and tables
 * Create new administrator account
 */

include_once '_basics.php';

class SetupClover extends OperateTables {

    private $sErrMsg = "";

    function checkLocalHost() {//whether visit from localhost;
        $whitelist = array('localhost', '127.0.0.1');

        if (!in_array($_SERVER['HTTP_HOST'], $whitelist)) {
            $this->sErrMsg.='Forbidden! Please visit this page using "localhost".<br/>';
            return false;
        } else {
            return true;
        }
    }

    function checkCloverNonexistent() {//clover exsit or not;if exist return false, or return ture;
        $sQuery = "SHOW TABLES FROM " . $this->sCloverDatabaseName;
        $rResult = mysql_query($sQuery) or $this->aErrors[] = "Database Error: cannot query mySQL database with the query: " . $sQuery;
        if ($rResult) {
            while ($row = mysql_fetch_row($rResult)) {
                if ($row[0] == "bst_users") {
                    $this->sErrMsg.='Forbidden! Clover already exists!<br/>';
                    return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }

    function checkInput(&$aData) {
        if (empty($aData['name'])) {
            $this->sErrMsg.="Please enter your name.<br/>";
        }
        if (!filter_var($aData['email'], FILTER_VALIDATE_EMAIL)) {
            $this->sErrMsg.="Please enter a validate email.<br/>";
        }
        if ($aData['password'] == "da39a3ee5e6b4b0d3255bfef95601890afd80709") {
            $this->sErrMsg.="Please enter a password.<br/>";
        }

        if ($aData['password'] != $aData['repeat_password']) {
            $this->sErrMsg.="Passwords you typed do not match.<br/>";
        }
    }

    function __construct() {
        $this->sTableName = "bst_users";
        $this->aaTableStructure = array_merge(BasicSystemTables::$aaDefault, BasicSystemTables::$bst_users);

        mysql_connect($this->sHost, $this->sUsername, $this->sPassword) or $this->aErrors[] = "Database Error: cannot connect to mySQL datebase!";
        //create clover database;
        $sQuery = "CREATE DATABASE IF NOT EXISTS`" . $this->sCloverDatabaseName . "` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
        $rResult = mysql_query($sQuery) or $this->aErrors[] = "Database Error: cannot query mySQL database with the query: " . $sQuery;

        if ($this->checkLocalHost() && $this->checkCloverNonexistent()) {
            $aaValues = json_decode($_POST["values"], true);
            $aKeys = json_decode($_POST['keys'], true);

            for ($i = 0; $i < count($aaValues); $i++) {
                for ($j = 0; $j < count($aKeys); $j++) {
                    $aaData[$i][$aKeys[$j]] = $aaValues[$i][$j];
                }
                $this->checkInput($aaData[$i]);
                if (empty($this->sErrMsg)) {
                    $aaData[$i]["id"] = "1";
                    $aaData[$i]["identity"] = "admin";
                    unset($aaData[$i]["repeat_password"]);
                    $aaData[$i]["password"] = sha256($aaData[$i]["password"]);
                }
            }
        }


        if (empty($this->sErrMsg)) {//all user information is correct;
            $this->setupCloverDatabase(); //creat all tables;
            $iResult = $this->addRecords($aaData); //add user;
            if ($iResult) {//admin account added;
                session_name("cloveriver");
                session_start();
                $_SESSION["user_id"] = 1; //login;
                $this->sSessionUserEmail = $aaData[0]["email"];
                $this->sSessionUserName = $aaData[0]["name"];
                $this->sSessionUserType = $aaData[0]["identity"];
                $this->iSessionUserId = 1;
                $aRlt["changed"] = 1;
            }
        } else {//user information incorrect;
            $aRlt["changed"] = 0;
            $aRlt["errors"] = $this->sErrMsg;
        }
        echo json_encode($aRlt);
    }

    function setupCloverDatabase() {
        mysql_select_db($this->sCloverDatabaseName) or $this->aErrors[] = "Database Error: cannot select the " . $this->sCloverDatabaseName . " database.";
        foreach (BasicSystemTables::$aTables as $table) {
            $sQuery = "CREATE TABLE IF NOT EXISTS `" . $table . "` (";
            $aaFields = array_merge(BasicSystemTables::$aaDefault, BasicSystemTables::${$table});
            {
                foreach ($aaFields as $aField) {
                    $sQuery.="`" . $aField["name"] . "` " . $aField["data_type"] . ",";
                }
            }
            $sQuery = substr($sQuery, 0, strlen($sQuery) - 1) . ") ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci";
            if (!$this->queryClover($sQuery)) {
                break;
            }
        }
    }

}

$cSetup = new SetupClover();
?>
