<?php

/* Contain basic classes that handle 
 * connection to Clover database
 * creations of Clvoer database
 * 
 * bst: basic system table
 * dbs: database services 
 */

function CheckUser() {
    if (!isset($_SESSION['user_id'])) {
        session_name("cloveriver");
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
    }
}

function Menu() {
    $cOpt = new OperateUsers();
    echo '<div id="menubar">
        <div id="menu">
    	<ul>
                <li class="clover">
                        <p class="cloversubtext"><a href="mailto:zhou.210@buckeyemail.osu.edu" title="Contact Xiao Zhou">Developed by<br/>© Xiao Zhou 2012</a></p>
		</li>                
		<li class="green">
			<p><a href="scheduler.php">Scheduler</a></p>
			<p class="subtext">Arrange your time</p>
		</li>
		<li class="orange">
			<p><a href="equipment.php">Equipment</a></p>
			<p class="subtext">What we can use</p>
		</li>
		<li class="red">
			<p><a href="equipment_users.php">User Rights</a></p>
			<p class="subtext">Who\'s using it</p>
		</li>
                <li class="cyan">
			<p><a href="roster.php">Roster</a></p>
			<p class="subtext">Meet your friends</p>
		</li>
                <li class="yellow">
			<p><a href="about.php">About</a></p>
			<p class="subtext">Know me more</p>
		</li>
                <li class="blue">
			<p><a href="help.php">Help</a></p>
			<p class="subtext">How may I help you</p>
		</li>                
                <li class="pink">
			<p>' . $cOpt->sSessionUserName . '</p>
			<p class="subtext"><a href="logout.php">Logout<br/>May I see you again</a></p>
		</li>
                    
	</ul>
        </div>
        <div id="menuright"></div>
        </div>
        <div id="messenger_dialog"><img/><label></label></div> 
        
   ';
}

function sha256($sString) {
    return hash("sha256", $sString);
}

class Connect2Clover {

    public $sUsername = "root";
    public $sPassword = "";
    public $sHost = "127.0.0.1";
    public $aErrors = array();
    public $sCloverDatabaseName = "cloveriver";
    public $timezone = "America/New_York";
    public $datetimeformat = "m/d/Y, g:i a";
    public $dateformat = "m/d/Y";
    public $sSessionUserType;
    public $sSessionUserName;
    public $sSessionUserEmail;
    public $iSessionUserId;

    function __construct() {
        date_default_timezone_set($this->timezone) or $this->aErrors[] = "System Error: cannot set timezone!";
        $rLink = mysql_connect($this->sHost, $this->sUsername, $this->sPassword) or
                $this->aErrors[] = "Database Error: cannot connect to mySQL datebase!";

        mysql_query("SET NAMES 'UTF8'");

        mysql_select_db($this->sCloverDatabaseName) or
                $this->aErrors[] = "Database Error: cannot select the Clover database!";

        //start sesssion;
        if (!isset($_SESSION['user_id'])) {
            session_name("cloveriver");
            session_start();
        }

        if (isset($_SESSION["user_id"])) {
            $sQuery = "SELECT * FROM `bst_users` WHERE `id`='" . $_SESSION["user_id"] . "' AND `identity`!='visitor' AND `deleted`='0'";
            $rResult = $this->queryClover($sQuery);
            if ($rResult) {
                $aRow = mysql_fetch_array($rResult);
                if ($aRow) {
                    $this->sSessionUserEmail = $aRow["email"];
                    $this->sSessionUserName = $aRow["name"];
                    $this->sSessionUserType = $aRow["identity"];
                    $this->iSessionUserId = $_SESSION["user_id"];
                }
            } else {
                $this->aErrors[] = "Database Error: cannot obtain user information!";
            }
        }
    }

    public function queryClover($sQuery) {
        $rResult = mysql_query($sQuery) or $this->aErrors[] = "Database Error: cannot query the Clover database with query: " . $sQuery;
        return $rResult;
    }

    function __destruct() {
        if (count($this->aErrors)) {

            foreach ($this->aErrors as &$sError) {
                print_r($sError . '<br/>');
            }
        }
    }

}

abstract class BasicSystemTables {

    public static $aTables = array("bst_users", "bst_equipment", "bst_user_equipment_rights", "bst_user_equipment_reservations");
    //bst=basic system table
    //
        //the users table structure
    public static $bst_users = array(
        "email" => array("name" => "email", "label" => "Email", "brief" => "3", "edit" => "3", "detail" => "3", "data_type" => "char(64) COLLATE utf8_unicode_ci NOT NULL", "function" => "email2Link(email)"), /* User ID: user email */
        "password" => array("name" => "password", "label" => "Password", "brief" => "0", "edit" => "4", "detail" => "0", "data_type" => "char(64)COLLATE utf8_unicode_ci NOT NULL"), /* Password */
        "name" => array("name" => "name", "label" => "Name", "brief" => "2", "edit" => "2", "detail" => "2", "data_type" => "varchar(128)COLLATE utf8_unicode_ci NOT NULL"), /* User's Name */
        "identity" => array("name" => "identity", "label" => "Identity", "brief" => "5", "edit" => "5", "detail" => "5", "data_type" => "enum('visitor','user','admin') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'visitor'"), /* user type */
        "description" => array("name" => "description", "label" => "Description", "brief" => "6", "edit" => "6", "detail" => "6", "data_type" => "varchar(128)COLLATE utf8_unicode_ci NOT NULL")
    );
    //the equipment structure
    public static $bst_equipment = array(
        "name" => array("name" => "name", "label" => "Equipment Name", "brief" => "3", "edit" => "3", "detail" => "3", "data_type" => "varchar(128) COLLATE utf8_unicode_ci NOT NULL"),
        "location" => array("name" => "location", "label" => "Equipment Location", "brief" => "4", "edit" => "4", "detail" => "4", "data_type" => "varchar(128)COLLATE utf8_unicode_ci NOT NULL"),
        "status" => array("name" => "status", "label" => "Status", "brief" => "5", "edit" => "5", "detail" => "5", "data_type" => "enum('normal','problematic','broken') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'normal'", "function" => "status2Icon(status)"),
        "status_detail" => array("name" => "status_detail", "label" => "Status Detail", "brief" => "6", "edit" => "6", "detail" => "6", "data_type" => "varchar(128) COLLATE utf8_unicode_ci NOT NULL"),
        "description" => array("name" => "description", "label" => "Description", "brief" => "7", "edit" => "7", "detail" => "7", "data_type" => "varchar(256)COLLATE utf8_unicode_ci NOT NULL")
    );
    //the user_equipment_rights structure
    public static $bst_user_equipment_rights = array(
        "user_id" => array("name" => "user_id", "label" => "User", "brief" => "3", "edit" => "0", "detail" => "3", "data_type" => "int(12) unsigned NOT NULL", "function" => "userID2Name(user_id)"),
        "equipment_id" => array("name" => "equipment_id", "label" => "Equipment", "brief" => "4", "edit" => "0", "detail" => "4", "data_type" => "int(12) unsigned NOT NULL", "function" => "equipmentID2Name(equipment_id)"),
        "position" => array("name" => "position", "label" => "Position", "brief" => "5", "edit" => "5", "detail" => "5", "data_type" => "enum('applicant','member','manager') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'applicant'"),
    );
    //the user_equipment_reseration structure
    public static $bst_user_equipment_reservations = array(
        "user_id" => array("name" => "user_id", "label" => "User ID", "brief" => "3", "edit" => "3", "detail" => "3", "data_type" => "int(12) unsigned NOT NULL", "function" => "userID2Name(user_id)"), /* User ID: user email */
        "equipment_id" => array("name" => "equipment_id", "label" => "Equipment ID", "brief" => "4", "edit" => "4", "detail" => "4", "data_type" => "int(12) unsigned NOT NULL"), /* Password */
        "date" => array("name" => "date", "label" => "Date", "brief" => "5", "edit" => "5", "detail" => "5", "data_type" => "DATE NOT NULL"), /* user type */
        "from" => array("name" => "from", "label" => "Reserved from", "brief" => "6", "edit" => "6", "detail" => "6", "data_type" => "INT( 2 ) NOT NULL"), /* user type */
        "to" => array("name" => "to", "label" => "Reserved to", "brief" => "7", "edit" => "7", "detail" => "7", "data_type" => "INT( 2 ) NOT NULL"), /* user type */
        "description" => array("name" => "description", "label" => "Description", "brief" => "8", "edit" => "8", "detail" => "8", "data_type" => "varchar(256)COLLATE utf8_unicode_ci NOT NULL", "function" => "comment2Icon(description)")
    );
    public static $aaDefault = array(
        "id" => array("name" => "id", "label" => "ID", "brief" => "1", "edit" => "0", "detail" => "1", "data_type" => "int(12) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)"),
        "deleted" => array("name" => "deleted", "brief" => "0", "edit" => "0", "detail" => "0", "label" => "Deleted", "data_type" => "int(12) unsigned NOT NULL DEFAULT '0'"),
        "last_modified_by" => array("name" => "last_modified_by", "brief" => "0", "edit" => "0", "detail" => "254", "label" => "Last Modified by", "data_type" => "int(12) unsigned NOT NULL DEFAULT '0'", "function" => "userID2Name(last_modified_by)"),
        "last_modified_on" => array("name" => "last_modified_on", "brief" => "0", "edit" => "0", "detail" => "255", "label" => "Last Modified on", "data_type" => "varchar(64)COLLATE utf8_unicode_ci NOT NULL")
            //array("name"=>"shared_with", "label"=>"Shared with", "data_type"=>"enum('not shared', 'group', 'public') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'not shared'"),
            //array("name"=>"share_policy", "label"=>"Share Policy", "data_type"=>"enum('name only','private', 'protected', 'public') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'name only'")
    );

}

class OperateTables extends Connect2Clover {

    public $sTableName; //database service name, ie. the table name.
    public $aaTableStructure = array();
    public $aActionIcons = array();
    public $aActionAuths = array();

    function __construct($sTableName, &$aaTableStructure) {
        parent::__construct();
        $this->sTableName = $sTableName;
        $this->aaTableStructure = array_merge(BasicSystemTables::$aaDefault, $aaTableStructure);
    }

    function getFields(&$aFields, $sColumn = "name", $sType = "system") {//type: system-all the fields, detail-detail view for users, edit-edit view for users, brief-brief view for users.
        $aTemp = array();
        foreach ($this->aaTableStructure as &$aStruct) {
            if ($sType == "system") {
                $aFields[] = $aStruct[$sColumn];
            } elseif ($aStruct[$sType] != 0) {
                $aTemp[$aStruct[$sType]] = $aStruct[$sColumn];
            }
        }

        //sort Fields;
        if ($sType == "system") {
            asort($aFields);
        } else {
            ksort($aTemp); //sort fields by their Key, which is the defined value in each view type.
            foreach ($aTemp as &$value) {
                $aFields[] = $value;
            }
        }
    }

    function checkIfRecordExist($aRecord) { //return ture if exist.
        $sQuery = "SELECT `id` FROM `" . $this->sTableName . "` WHERE `deleted`=0 AND ";
        $aTemp = array();
        foreach ($aRecord as $key => $value) {
            $aTemp[] = "`" . $key . "`='" . mysql_real_escape_string($value) . "'";
        }
        $sQuery.=implode(" AND ", $aTemp);
        $rResult = $this->queryClover($sQuery);
        return mysql_num_rows($rResult);
    }

    function checkIfRecordIDExist($iId) {
        $sQuery = "SELECT `id` FROM `" . $this->sTableName . "` WHERE `id`='" . $iId . "'";
        $rResult = $this->queryClover($sQuery);
        return mysql_num_rows($rResult);
    }

    public function userID2Name($index, &$aRow) {
        $iId = $aRow[$index];
        $sQuery = "SELECT name, email FROM bst_users WHERE `deleted`=0 AND `id`='" . $iId . "'";
        $rResult = $this->queryClover($sQuery);
        $row = mysql_fetch_array($rResult);
        if ($row) {
            return '<a href="mailto:' . $row["email"] . '" title="Contact ' . $row["name"] . ': ' . $row["email"] . '">' . $row["name"] . '</a>';
        } else
            return "User was deleted";
    }

    public function comment2Icon($index, &$aRow) {
        if ($aRow[$index] == "") {
            return "";
        } else {
            return '<img src="icons/comment.png" title="' . $aRow[$index] . '"/>';
        }
    }

    public function equipmentID2Name($index, &$aRow) {
        $iId = $aRow[$index];
        $sQuery = "SELECT name FROM bst_equipment WHERE `deleted`=0 AND `id`='" . $iId . "'";
        $rResult = $this->queryClover($sQuery);
        if ($rResult) {
            $row = mysql_fetch_array($rResult);
            if ($row) {
                return $row["name"];
            } else {
                return "Equipment was deleted";
            }
        } else {
            return $iId;
        }
    }

    public function readRecord($id, $sType = "system", $withLabel = false) {
        $aResult = array();
        $aColumns = array();
        $aLabel = array();

        $this->getFields($aColumns, "name", $sType);
        $this->getFields($aLabel, "label", $sType);
        $sQuery = "SELECT " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
		FROM " . $this->sTableName . " WHERE `id`='" . $id . "'";
        $rResult = $this->queryClover($sQuery);
        while ($aRow = mysql_fetch_array($rResult)) {
            for ($i = 0; $i < count($aColumns); $i++) {
                if (isset($this->aaTableStructure[$aColumns[$i]]["function"]) && $withLabel) {
                    $aResult[($withLabel ? $aLabel[$i] : $aColumns[$i])] = $this->inLineFunction($this->aaTableStructure[$aColumns[$i]]["function"], $aRow);
                } else {
                    $aResult[($withLabel ? $aLabel[$i] : $aColumns[$i])] = $aRow[$aColumns[$i]];
                }
            }
        }
        return $aResult;
    }

    public function addRecords(&$aaData) {//return inserted id;
        if (count($aaData)) {
            $aName = array();
            $this->getFields($aName);
            $sQuery = "INSERT INTO `" . $this->sTableName . "` ( `" . implode("`, `", $aName) . "` ) VALUES ";
            $aTemp = array();
            foreach ($aaData as &$aRecord) {
                $aRecord["id"] = "";
                $aRecord["deleted"] = 0;
                $aRecord["last_modified_by"] = $this->iSessionUserId;
                $aRecord["last_modified_on"] = date($this->datetimeformat);
                foreach ($aRecord as &$sData) {
                    $sData = mysql_real_escape_string($sData);
                }
                ksort($aRecord);
                $aTemp[] = "('" . implode("', '", $aRecord) . "')";
            }
            $sQuery.=implode(",", $aTemp);
            $this->queryClover($sQuery);
            return mysql_insert_id();
        } else {
            return 0;
        }
    }

    public function deleteRecords(&$aIds) {//return the number of the successfully deleted records.
        $iResult = 0;
        foreach ($aIds as &$iId) {
            $sQuery = "UPDATE `" . $this->sTableName . "` SET `last_modified_by` ='" . mysql_real_escape_string($this->iSessionUserId) . "', `last_modified_on`='" . date($this->datetimeformat) . "', `deleted`=" . $iId . " WHERE `id`='" . $iId . "'";
            if ($this->queryClover($sQuery)) {
                $iResult++;
            }
        }
        return $iResult;
    }

    public function checkIdentity($sRules, &$aRow) {
        $aRules = explode(",", $sRules);
        if (in_array($this->sSessionUserType, $aRules) !== false) {
            return true;
        } elseif ((in_array("self", $aRules) !== false) && (isset($aRow["email"]) ? ($aRow["email"] == $this->sSessionUserEmail) : ($aRow["user_id"] == $this->iSessionUserId))) {
            return true;
        }
    }

    public function checkPosition($sRules, &$aRow) {
        $position = "";
        $sQuery = "SELECT `position`
            FROM bst_user_equipment_rights 
            WHERE `deleted`=0 AND `equipment_id`=" . $aRow['equipment_id'] . " AND user_id=$this->iSessionUserId";
        $rResult = $this->queryClover($sQuery);

        if ($rResult) {
            while ($row = mysql_fetch_array($rResult)) {
                $position = $row["position"];
            }
            $aRules = explode(",", $sRules);
            return (in_array($position, $aRules) !== false);
        } else {
            return false;
        }
    }

    public function inLineFunction($sFunction, &$aRow) {//$aRow is the Row from table search;
        $pattern = '/([\|\&\.])?(!)?([^\|\&\.]+)/';
        preg_match_all($pattern, $sFunction, $aaMatches, PREG_SET_ORDER);

        $pattern = '/([^\(\)]+)\(([^\(\)]+)?\)/';
        $bResult;


        foreach ($aaMatches as &$aMatch) {
            preg_match($pattern, $aMatch[3], $aFn);
            if ($aMatch[1] == "|") {
                $bResult = $bResult || ($aMatch[2] == "!" ? !$this->$aFn[1]($aFn[2], $aRow) : $this->$aFn[1]($aFn[2], $aRow));
            } elseif ($aMatch[1] == "&") {
                $bResult = $bResult && ($aMatch[2] == "!" ? !$this->$aFn[1]($aFn[2], $aRow) : $this->$aFn[1]($aFn[2], $aRow));
            } elseif ($aMatch[1] == ".") {
                $bResult = $bResult . $this->$aFn[1]($aFn[2], $aRow);
            } else {
                $bResult = ($aMatch[2] == "!" ? !$this->$aFn[1]($aFn[2], $aRow) : $this->$aFn[1]($aFn[2], $aRow));
            }
        }
        return $bResult;
    }

    public function updateRecords(&$aaData) { //return the number of the successfully updated records
        $iResult = 0;
        foreach ($aaData as &$aRecord) {
            // insert new records;
            if ($this->checkIfRecordIDExist($aRecord['id'])) {
                //maker a new copy of the old record;
                $aOldRecord = $this->readRecord($aRecord['id'], "system", FALSE);
                $aaRecords = array($aOldRecord);
                $iLastId = $this->addRecords($aaRecords);
                //change the deleted field of the new copy to its original ID; this will serve as the deleted one in the trash can;
                $sQuery = "UPDATE `" . $this->sTableName . "` SET `deleted`=" . $aRecord['id'] . " WHERE `id`='" . $iLastId . "'";
                $this->queryClover($sQuery);

                //update the old record information;
                $sQuery = "UPDATE `" . $this->sTableName . "` SET ";
                foreach ($aRecord as $sKey => &$sValue) {
                    $sQuery.="`" . $sKey . "`='" . mysql_real_escape_string($sValue) . "',";
                }
                $sQuery.="`last_modified_by`='" . $this->iSessionUserId . "',";
                $sQuery.="`last_modified_on`='" . mysql_real_escape_string(date($this->datetimeformat)) . "'";
                $sQuery.=" WHERE `id`='" . $aRecord['id'] . "'";
                $this->queryClover($sQuery);
                $iResult++;
            }
        }
        return $iResult;
    }

    public function AjaxSearch() {
        /*
         * Script:    DataTables server-side script for PHP and MySQL
         * Copyright: 2010 - Allan Jardine
         * License:   GPL v2 or BSD (3-point)
         */

        /*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * Easy set variables
         */

        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        //Get the Columns of the table.
        $aColumns = array();
        $this->getFields($aColumns, "name", "brief");

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "id";

        /* DB table to use */
        $sTable = $this->sTableName;

        /*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * If you just want to use the basic configuration for DataTables with PHP server-side, there is
         * no need to edit below this line
         */

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . mysql_real_escape_string($_GET['iDisplayStart']) . ", " .
                    mysql_real_escape_string($_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if (isset($_GET['iSortCol_0'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i]) - 1] . " 
				 	" . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", "; //intval( $_GET['iSortCol_'.$i])-1, the -1 is to cancel out the first actions column;
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "WHERE (`deleted`='0')";
        if ($_GET['sSearch'] != "") {
            $sWhere = "WHERE (`deleted`='0') AND (";
            for ($i = 0; $i < count($aColumns); $i++) {
                $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }
        }


        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
		FROM   $sTable
		$sWhere
		$sOrder
		$sLimit
	";
        $rResult = $this->queryClover($sQuery);

        /* Data set length after filtering */
        $sQuery = "
		SELECT FOUND_ROWS()
	";
        $rResultFilterTotal = $this->queryClover($sQuery);
        $aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
        $iFilteredTotal = $aResultFilterTotal[0];

        /* Total data set length */
        $sQuery = "
		SELECT COUNT(" . $sIndexColumn . ")
		FROM   $sTable
	";
        $rResultTotal = $this->queryClover($sQuery);
        $aResultTotal = mysql_fetch_array($rResultTotal);
        $iTotal = $aResultTotal[0];


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        while ($aRow = mysql_fetch_array($rResult)) {
            $row = array();
            $sAct = "";
            /* put actions icons here for each row;
              foreach ($this->aActionIcons as $sKey=>&$aActions){
              $aRules=explode(",", $this->aActionAuths[$sKey]);

              if(in_array($this->sSessionUserType, $aRules)){
              $sAct.=$aActions;
              }
              elseif(in_array("self", $aRules)&&$aRow["email"]==$this->sSessionUserEmail){
              $sAct.=$aActions;
              }
              }
              $row[]=$sAct;
             */

            foreach ($this->aActionIcons as $sKey => &$Action) {

                if ($this->inLineFunction($this->aActionAuths[$sKey], $aRow)) {
                    $sAct.=$Action;
                }
            }
            $row[] = $sAct;

            for ($i = 0; $i < count($aColumns); $i++) {

                if (isset($this->aaTableStructure[$aColumns[$i]]["function"])) {
                    $row[] = $this->inLineFunction($this->aaTableStructure[$aColumns[$i]]["function"], $aRow);
                } else {
                    $row[] = $aRow[$aColumns[$i]];
                }
            }
            $output['aaData'][] = $row;
        }

        return json_encode($output);
    }

    public function AjaxAdd(&$aaData) {
        $iResult = $this->addRecords($aaData);
        $aResult = array("changed" => $iResult);
        return json_encode($aResult);
    }

    public function AjaxDelete(&$aIds) {
        $iResult = $this->deleteRecords($aIds);
        $aResult = array("changed" => $iResult);
        return json_encode($aResult);
    }

    public function AjaxUpdate(&$aaData) {
        $iResult = $this->updateRecords($aaData);
        $aResult = array("changed" => $iResult);
        return json_encode($aResult);
    }

    public function AjaxRead($id, $sType, $withlabel = false) {//read the field from a record whose id is $id according to $sType--detail, brief, detail, system;
        return json_encode($this->readRecord($id, $sType, $withlabel));
    }

}

class OperateUsers extends OperateTables {

    function __construct() {
        parent::__construct('bst_users', BasicSystemTables::$bst_users);
        $this->aActionIcons["detail"] = '<img src="icons/page_white_text_width.png" name="action_close" title="Show/Hide details">';
        $this->aActionIcons["edit"] = '<img src="icons/page_white_edit.png" name="action_edit" title="Edit this user">';
        $this->aActionIcons["delete"] = '<img src="icons/cross.png" name="action_delete" title="Delete this user">';
        $this->aActionIcons["password"] = '<img src="icons/key.png" name="action_password" title="Change the password of this user">';
        $this->aActionAuths["detail"] = 'checkIdentity(user,admin)';
        $this->aActionAuths["edit"] = 'checkIdentity(self,admin)';
        $this->aActionAuths["delete"] = 'checkIdentity(admin)';
        $this->aActionAuths["password"] = 'checkIdentity(self)';
    }

    public function Login($sEmail, $sPassword) {//return the id of the user or zero if not logged in;
        $aRecord["email"] = $sEmail;
        $aRecord["password"] = sha256($sPassword);
        $aRecord["deleted"] = "0";
        $sQuery = "SELECT * FROM `" . $this->sTableName . "` WHERE ";
        $aTemp = array();
        foreach ($aRecord as $key => $value) {
            $aTemp[] = "`" . $key . "`='" . mysql_real_escape_string($value) . "'";
        }
        $sQuery.=implode(" AND ", $aTemp);
        $rResult = $this->queryClover($sQuery);
        if (mysql_num_rows($rResult)) {
            $aRow = mysql_fetch_array($rResult);
            $iId = $aRow["id"];
            $sIdentity = $aRow["identity"];
            if ($sIdentity == "visitor") {
                return "You have a visitor account. Please wait for activation by the administrator ";
            } else {
                $this->sSessionUserEmail = $aRow["email"];
                $this->sSessionUserName = $aRow["name"];
                $this->sSessionUserType = $aRow["identity"];
                $this->iSessionUserId = $iId;
                $_SESSION["user_id"] = $iId;
                return "OK";
            }
        } else {
            return "User information does not match. Please try again.";
        }
    }

    function checkIfUserExist($email) {
        $aEmail = array("email" => $email, "deleted" => '0');
        return $this->checkIfRecordExist($aEmail);
    }

    public function AddUser($aUser) {
        if (!$this->checkIfUserExist($aUser["email"])) {
            $aaUser = array($aUser);
            $this->addRecords($aaUser);
            return true; //added
        } else {
            return false; //user already exists;
        }
    }

    public function UpdateUser($aaUsers) {
        return $this->updateRecords($aaUsers);
    }

    public function ReadUser($iId) {
        return $this->readRecord($iId, "edit", FALSE);
    }

    public function DeleteUser(&$aIds) {
        return $this->deleteRecords($aIds);
    }

    public function email2Link($index, $aRow) {
        return '<a href="mailto:' . $aRow[$index] . '">' . $aRow[$index] . '</a>';
    }

    public function AjaxGenerateEmailList() {
        $sQuery = "SELECT email 
            FROM bst_users WHERE deleted=0 AND identity!='visitor'";
        $sList = "";
        $rResult = $this->queryClover($sQuery);
        if ($rResult) {
            while ($aRow = mysql_fetch_array($rResult)) {
                $sList.=$aRow["email"] . ";";
            }
            $aData = Array("changed" => 1, "emaillist" => $sList);
        } else {
            $aData = Array("changed" => 0);
        }

        return json_encode($aData);
    }

}

class OperateEquipment extends OperateTables {

    public function __construct() {
        parent::__construct("bst_equipment", BasicSystemTables::$bst_equipment);
        $this->aActionIcons["detail"] = '<img src="icons/page_white_text_width.png" name="action_close" title="Show/Hide details">';
        $this->aActionIcons["edit"] = '<img src="icons/page_white_edit.png" name="action_edit" title="Edit this equipment">';
        $this->aActionIcons["delete"] = '<img src="icons/cross.png" name="action_delete" title="Delete this equipment">';
        $this->aActionIcons["apply"] = '<img src="icons/user_go.png" name="action_apply" title="Apply for using this equipment">';
        $this->aActionIcons["reserve"] = '<img src="icons/tag_blue_add.png" name="action_reserve" title="Reserve this equipment">';
        $this->aActionIcons["emaillist"] = '<img src="icons/email.png" name="action_emaillist" title="Generate email list of the user group of this equipment">';
        $this->aActionAuths["detail"] = 'checkIdentity(user,admin)';
        $this->aActionAuths["edit"] = 'checkIdentity(admin)|checkPosition(manager)';
        $this->aActionAuths["delete"] = 'checkIdentity(admin)';
        $this->aActionAuths["reserve"] = 'checkPosition(member,manager)';
        $this->aActionAuths["apply"] = '!checkPosition(applicant,member,manager)';
        $this->aActionAuths["emaillist"] = 'checkIdentity(admin)|checkPosition(manager)';
    }

    public function checkPosition($sRules, &$aRow) {
        $aRules = explode(",", $sRules);
        $sPosition = $this->EquipmentId2UserPosition($aRow["id"]); //the id here is the equipment id;
        return in_array($sPosition, $aRules);
    }

    public function EquipmentId2UserPosition($iEquipmentId) {
        $sQuery = "SELECT `position` FROM `bst_user_equipment_rights` WHERE `deleted`=0 AND `user_id`='" . $this->iSessionUserId . "' AND `equipment_id`='" . $iEquipmentId . "'";
        $rResult = $this->queryClover($sQuery);
        if ($rResult) {
            while ($row = mysql_fetch_array($rResult)) {
                return $row["position"];
            }
        }
    }

    public function status2Icon($index, $aRow) {
        if ($aRow[$index] == "normal") {
            return '<img src="icons/accept.png" title="Normal"/>';
        } elseif ($aRow[$index] == "problematic") {
            return '<img src="icons/bug_error.png" title="Problematic"/>';
        } elseif ($aRow[$index] == "broken") {
            return '<img src="icons/exclamation.png" title="Broken"/>';
        }
    }

    public function AjaxGenerateEquipmentUserGroupEmailList($iEquipment_id) {
        $sQuery = "SELECT bst_users.email 
            FROM bst_user_equipment_rights 
            LEFT JOIN bst_users ON bst_user_equipment_rights.user_id=bst_users.id 
            WHERE bst_users.deleted=0 AND 
                bst_user_equipment_rights.deleted=0 AND
                bst_user_equipment_rights.position!='applicant' AND
                bst_user_equipment_rights.equipment_id=$iEquipment_id";
        $sList = "";
        $rResult = $this->queryClover($sQuery);
        if ($rResult) {
            while ($aRow = mysql_fetch_array($rResult)) {
                $sList.=$aRow["email"] . ";";
            }
            $aData = Array("changed" => 1, "emaillist" => $sList);
        } else {
            $aData = Array("changed" => 0);
        }
        return json_encode($aData);
    }

}

class OperateUserEquipmentRights extends OperateTables {

    public function __construct() {
        parent::__construct("bst_user_equipment_rights", BasicSystemTables::$bst_user_equipment_rights);
        $this->aActionIcons["change_position"] = '<img src="icons/user_edit.png" name="action_change_position" title="Change the Position of this user in this equipment\'s user group">';
        $this->aActionIcons["delete"] = '<img src="icons/delete.png" name="action_delete" title="Remove this user from the user group of this equipment">';
        $this->aActionAuths["change_position"] = 'checkIdentity(admin)||checkPosition(manager)';
        $this->aActionAuths["delete"] = 'checkIdentity(admin)||checkPosition(manager)';
    }

    public function AjaxSearch() {
        $aColumns = array();
        $this->getFields($aColumns, "name", "brief");
        $atColumns = Array();
        foreach ($aColumns as &$Col) {
            $atColumns[] = "bst_user_equipment_rights." . $Col;
        }

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "id";

        /* DB table to use */
        $sTable = $this->sTableName;

        /*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * If you just want to use the basic configuration for DataTables with PHP server-side, there is
         * no need to edit below this line
         */

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . mysql_real_escape_string($_GET['iDisplayStart']) . ", " .
                    mysql_real_escape_string($_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if (isset($_GET['iSortCol_0'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i]) - 1] . " 
				 	" . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", "; //intval( $_GET['iSortCol_'.$i])-1, the -1 is to cancel out the first actions column;
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }
        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $aEColumns = Array(
            "bst_users.name",
            "bst_equipment.name",
            "bst_user_equipment_rights.position"
        );
        $sWhere = "WHERE (bst_users.deleted=0) AND (bst_equipment.deleted=0) AND (bst_user_equipment_rights.deleted=0)";
        if ($_GET['sSearch'] != "") {
            $sWhere = "WHERE (bst_users.deleted=0) AND (bst_equipment.deleted=0) AND (bst_user_equipment_rights.deleted=0) AND (";
            for ($i = 0; $i < count($aEColumns); $i++) {
                $sWhere .= $aEColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $atColumns)) . "
		FROM  bst_users RIGHT JOIN bst_user_equipment_rights ON bst_users.id=bst_user_equipment_rights.user_id
                LEFT JOIN bst_equipment ON bst_user_equipment_rights.equipment_id=bst_equipment.id 
		$sWhere
		$sOrder
		$sLimit
	";
        $rResult = $this->queryClover($sQuery);

        /* Data set length after filtering */
        $sQuery = "
		SELECT FOUND_ROWS()
	";
        $rResultFilterTotal = $this->queryClover($sQuery);
        $aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
        $iFilteredTotal = $aResultFilterTotal[0];

        /* Total data set length */
        $sQuery = "
		SELECT COUNT(" . $sIndexColumn . ")
		FROM   $sTable WHERE `deleted`=0;
	";
        $rResultTotal = $this->queryClover($sQuery);
        $aResultTotal = mysql_fetch_array($rResultTotal);
        $iTotal = $aResultTotal[0];


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        while ($aRow = mysql_fetch_array($rResult)) {
            $row = array();
            $sAct = "";
            foreach ($this->aActionIcons as $sKey => &$Action) {

                if ($this->inLineFunction($this->aActionAuths[$sKey], $aRow)) {
                    $sAct.=$Action;
                }
            }
            $row[] = $sAct;

            for ($i = 0; $i < count($aColumns); $i++) {

                if (isset($this->aaTableStructure[$aColumns[$i]]["function"])) {
                    $row[] = $this->inLineFunction($this->aaTableStructure[$aColumns[$i]]["function"], $aRow);
                } else {
                    $row[] = $aRow[$aColumns[$i]];
                }
            }
            $output['aaData'][] = $row;
        }

        return json_encode($output);
    }

}

class OperateUserEquipmentReservations extends OperateTables {

    public function __construct() {
        parent::__construct("bst_user_equipment_reservations", BasicSystemTables::$bst_user_equipment_reservations);
        $this->aActionIcons["delete"] = '<img src="icons/cross.png" name="action_delete_reservation" title="Delete this reservation">';
        $this->aActionAuths["delete"] = 'checkIdentity(admin,self)||checkPosition(manager)';
    }

    public function checkTimeConflict($iEquipment_id, $sDate, $iFrom, $iTo) {
        $sQuery = "SELECT `from`, `to` 
            FROM bst_user_equipment_reservations 
            WHERE `deleted`=0 AND `equipment_id`=$iEquipment_id AND `date`='$sDate'
            AND (
            (`from`<$iFrom AND `to`>$iFrom) 
            OR 
            (`from`<$iTo AND `to`>$iTo)
            OR
            (`from`>=$iFrom AND `to`<=$iTo)
            )";
        $result = $this->queryClover($sQuery);
        return mysql_num_rows($result);
    }

    public function AjaxGetReservation($sDate, $equipment_id) {//date in YYYY-mm-dd format;
        $aColumns = array();
        $this->getFields($aColumns, "name", "brief");
        $sQuery = "SELECT `" . str_replace(" , ", " ", implode("`, `", $aColumns)) . "`
            FROM bst_user_equipment_reservations 
           WHERE `deleted`=0 AND `date`='$sDate' AND `equipment_id`=$equipment_id" . " ORDER BY `from`";
        $output = array();

        $rResult = $this->queryClover($sQuery);
        while ($aRow = mysql_fetch_array($rResult)) {
            $sAct = "";
            foreach ($this->aActionIcons as $sKey => &$Action) {

                if ($this->inLineFunction($this->aActionAuths[$sKey], $aRow)) {
                    $sAct.=$Action;
                }
            }
            $row["actions"] = $sAct;

            for ($i = 0; $i < count($aColumns); $i++) {

                if (isset($this->aaTableStructure[$aColumns[$i]]["function"])) {
                    $row[$aColumns[$i]] = $this->inLineFunction($this->aaTableStructure[$aColumns[$i]]["function"], $aRow);
                } else {
                    $row[$aColumns[$i]] = $aRow[$aColumns[$i]];
                }
            }
            $output[] = $row;
        }
        return json_encode($output);
    }

}

class OperateMyEquipment extends OperateEquipment {

    public function __construct() {
        parent::__construct();
    }

    public function getFields(&$aFields, $sColumn = "name", $sType = "system") {
        parent::getFields($aFields, $sColumn, $sType);
        if ($sType == "brief" || $sType == "detail")
            $aFields[] = BasicSystemTables::$bst_user_equipment_rights["position"][$sColumn];
    }

    public function getAuthEquipment() {
        $sQuery = "SELECT bst_equipment.id, bst_equipment.name 
            FROM bst_equipment RIGHT JOIN bst_user_equipment_rights ON bst_equipment.id=bst_user_equipment_rights.equipment_id 
            WHERE bst_equipment.deleted=0 AND bst_user_equipment_rights.deleted=0 AND bst_user_equipment_rights.position!='applicant' AND bst_user_equipment_rights.user_id=" . $this->iSessionUserId;
        $rResult = $this->queryClover($sQuery);
        $aEquip = Array();
        if ($rResult) {
            while ($row = mysql_fetch_array($rResult)) {
                $aEquip[] = Array("id" => $row["id"], "name" => $row["name"]);
            }
        }
        return $aEquip;
    }

    public function AjaxSearch() {
        $aColumns = array();
        $this->getFields($aColumns, "name", "brief");

        /* Indexed column (used for fast and accurate table cardinality) */
        $sIndexColumn = "id";

        /* DB table to use */
        $sTable = $this->sTableName;

        /*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
         * If you just want to use the basic configuration for DataTables with PHP server-side, there is
         * no need to edit below this line
         */

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . mysql_real_escape_string($_GET['iDisplayStart']) . ", " .
                    mysql_real_escape_string($_GET['iDisplayLength']);
        }


        /*
         * Ordering
         */
        if (isset($_GET['iSortCol_0'])) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i]) - 1] . " 
				 	" . mysql_real_escape_string($_GET['sSortDir_' . $i]) . ", "; //intval( $_GET['iSortCol_'.$i])-1, the -1 is to cancel out the first actions column;
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sWhere = "WHERE ($sTable.deleted=0) AND (bst_user_equipment_rights.deleted=0) AND (bst_user_equipment_rights.user_id=$this->iSessionUserId)";
        if ($_GET['sSearch'] != "") {
            $sWhere = "WHERE ($sTable.deleted=0) AND (bst_user_equipment_rights.deleted=0) AND (bst_user_equipment_rights.user_id=$this->iSessionUserId) AND (";
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == "id") {
                    $sWhere .= "bst_equipment." . $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
                } else {
                    $sWhere .=$aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
                }
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= $sTable . "." . $aColumns[$i] . " LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }
        }


        /*
         * SQL queries
         * Get data to display
         */
        $sQuery = "
		SELECT SQL_CALC_FOUND_ROWS $sTable." . str_replace(" , ", " ", implode(", ", $aColumns)) . "
		FROM   $sTable RIGHT JOIN bst_user_equipment_rights ON $sTable.id=bst_user_equipment_rights.equipment_id
		$sWhere
		$sOrder
		$sLimit
	";
        $rResult = $this->queryClover($sQuery);

        /* Data set length after filtering */
        $sQuery = "
		SELECT FOUND_ROWS()
	";
        $rResultFilterTotal = $this->queryClover($sQuery);
        $aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
        $iFilteredTotal = $aResultFilterTotal[0];

        /* Total data set length */
        $sQuery = "
		SELECT COUNT(" . $sIndexColumn . ")
		FROM   $sTable
	";
        $rResultTotal = $this->queryClover($sQuery);
        $aResultTotal = mysql_fetch_array($rResultTotal);
        $iTotal = $aResultTotal[0];


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        while ($aRow = mysql_fetch_array($rResult)) {
            $row = array();
            $sAct = "";
            /* put actions icons here for each row;
              foreach ($this->aActionIcons as $sKey=>&$aActions){
              $aRules=explode(",", $this->aActionAuths[$sKey]);

              if(in_array($this->sSessionUserType, $aRules)){
              $sAct.=$aActions;
              }
              elseif(in_array("self", $aRules)&&$aRow["email"]==$this->sSessionUserEmail){
              $sAct.=$aActions;
              }
              }
              $row[]=$sAct;
             */

            foreach ($this->aActionIcons as $sKey => &$Action) {

                if ($this->inLineFunction($this->aActionAuths[$sKey], $aRow)) {
                    $sAct.=$Action;
                }
            }
            $row[] = $sAct;

            for ($i = 0; $i < count($aColumns); $i++) {

                if (isset($this->aaTableStructure[$aColumns[$i]]["function"])) {
                    $row[] = $this->inLineFunction($this->aaTableStructure[$aColumns[$i]]["function"], $aRow);
                } else {
                    $row[] = $aRow[$aColumns[$i]];
                }
            }
            $output['aaData'][] = $row;
        }

        return json_encode($output);
    }

}

?>
