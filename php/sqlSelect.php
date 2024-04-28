<?php

$datum = $_GET['date'];
$datumN = date('Y-m-d', strtotime($datum . ' + 1 day'));
$datum = $datum . " 00:00:00";
$datumN = $datumN . " 00:00:00";

$servername = "localhost";
$username = "root";
$password = "";

$conn = mysqli_connect($servername, $username, $password, "diplomska");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql = "SELECT *
        FROM el15prejeto
        WHERE timestamp BETWEEN '$datum' AND '$datumN'
        LIMIT 96";

$result = mysqli_query($conn, $sql);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC); // list arrays with values only in rows
$value = array();
foreach ($rows as $rowl) {
    $value[$rowl['timestamp']] = $rowl['value'];
}

echo json_encode($rows);

