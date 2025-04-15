<?php

require_once 'b2config.php';

$connexion = @mysqli_connect($server, $loginsql, $passsql) or die("Can't connect to the database<br>");
mysqli_select_db($connexion, $base);

function veriflog()
{
    global $user_login, $connexion, $tableusers;

    $user_login = $_COOKIE["cafeloguser"] ?? null;
    $user_pass_md5 = $_COOKIE["cafelogpass"] ?? null;

    if (!($user_login && $user_pass_md5)) {
        return false;
    }

    $query = " SELECT user_login, user_pass FROM $tableusers WHERE user_login = '$user_login' ";
    $result = @mysqli_query($connexion, $query) or die("Query: $query<br /><br />Error: " . mysqli_error($connexion));

    $lines = mysqli_num_rows($result);
    if ($lines < 1) {
        return false;
    }

    $res = mysqli_fetch_row($result);
    return $res[0] === $user_login && md5($res[1]) === $user_pass_md5;
}

if (!veriflog()) {
    if (!empty($_COOKIE["cafeloguser"])) {
        $error = "<b>Error</b>: wrong login or password";
    }
    include "b2login.php";
    exit();
}