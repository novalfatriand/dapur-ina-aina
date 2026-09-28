<?php

    session_start();

    if (isset($_SESSION["user"])) {
        if ($_SESSION["user"]["role"] === "administrator") {
            header("Location: admin/index.php");
        } else {
            header("Location: kasir/index.php");
        }
        exit;
    }

    header("Location: customer/menu.php");
    exit;
    
?>
