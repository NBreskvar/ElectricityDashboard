<?php
require_once  '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
function solaredge($datum)
{
    $api_key = $_ENV['SOLAREDGE_API_KEY'];
    $site_id = $_ENV['SITE_ID'];

    $url = "https://monitoringapi.solaredge.com/site/" . $site_id . "/energy?timeUnit=DAY&endDate=" . urlencode($datum) . "&startDate=" . urlencode($datum) . "&api_key=" . $api_key;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //ne preveri SSL certifikata
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $resp = curl_exec($ch);                //curl odgovor api klica
    file_put_contents('data', $resp);   //shrani curl odgovor v datoteko
    $resp = file_get_contents('data'); 

    if ($e = curl_error($ch)) {
        echo $e;
        return;
    }
    $decoded = json_decode($resp, true); //true spremeni object v array

    foreach ($decoded['energy']['values'] as $x => $y) {
        foreach ($y as $var => $val) {
            if ($var == 'value') {
                return $val / 1000;
            }
        }
    }
}

