<?php

/*logout
 */
session_name("cloveriver");
session_start();
unset($_SESSION["user_id"]);
session_unset();
session_destroy();
echo json_encode("OK");
?>
