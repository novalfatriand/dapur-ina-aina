<?php

    $HOSTNAME   = "localhost";
    $USERNAME   = "root";
    $PASSWORD   = "";
    $DB_NAME    = "dapur_ina_aina";

    $connection = new mysqli($HOSTNAME, $USERNAME, $PASSWORD, $DB_NAME);

    if ($connection->connect_error) {
        die("Koneksi database gagal: " . $connection->connect_error);
    }

    $connection->set_charset("utf8mb4");

?>
