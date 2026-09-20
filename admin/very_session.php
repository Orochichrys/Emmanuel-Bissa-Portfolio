<?php 

    require_once __DIR__ . "/../includes/functions.php";

    if(!isset($_SESSION["admin_id"])){
        redirectTo("login.php");
    }

?>