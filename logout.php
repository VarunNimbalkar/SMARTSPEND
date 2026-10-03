<?php

require "config.php";


/* Destroy login session */

session_unset();

session_destroy();


/* Go back to Login */

header("Location: login.php");

exit;

?>