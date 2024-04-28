<?php
include 'solaredge.php';
include 'elektro.php'; 
include 'elektroSQL.php';
$datum = isset($_GET['date']) ? $_GET['date'] : "2023-09-19";

$prevzeta = elektro(0,$datum);
$oddana= elektro(1,$datum);
$proiz = solaredge($datum);
/* $prevzeta = elektroSQL(0,$datum); */
/* $oddana = elektroSQL(1, $datum); */
/* $proiz = elektroSQL(3,$datum) / 1000; */

$hisa = $proiz + $prevzeta - $oddana;

$data = [
    'prevzeta' => $prevzeta,
    'oddana' => $oddana,
    'proiz' => $proiz,  
    'hisa' => $hisa
];

echo json_encode($data);
