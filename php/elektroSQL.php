<?php
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

function elektroSQL($opt, $datum)
{
    $st = [];
    $datum = $_GET['date'];
    $datumN = date('Y-m-d', strtotime($datum . ' + 2 day'));
    $datum = $datum . " 00:00:00";
    $datumN = $datumN . " 00:00:00";

    $servername = "localhost";
    $username = "root";
    $password = "";

    $conn = mysqli_connect($servername, $username, $password, "diplomska");

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $dTypes = [
        "elprejeto",
        "eloddano",
        "el15prejeto",
        "solaredge"
    ];
    $dType = "";

    $dType = $dTypes[$opt];

    $sql = "SELECT *
        FROM " . $dType . "
        WHERE timestamp BETWEEN '$datum' AND '$datumN';";

    $result = mysqli_query($conn, $sql);
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

    foreach ($rows as $x => $y) {
        $st[$x] = $y['value'];
    }
    if ($opt == 3)
        return $st[0];
    $sest = $st[1] - $st[0];
    return $sest;
}
