<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void
{
    if (!isset($_SESSION["user"])) {
        header("Location: ../login.php");
        exit;
    }
}

function requireRole(string $role): void
{
    requireLogin();

    if ($_SESSION["user"]["role"] !== $role) {
        header("Location: ../index.php");
        exit;
    }
}
?>
