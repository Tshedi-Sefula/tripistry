<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION["user_id"]);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function isTraveller() {
    return isset($_SESSION["role"]) && $_SESSION["role"] === "traveller";
}

function isAgency() {
    return isset($_SESSION["role"]) && $_SESSION["role"] === "agency";
}
?>