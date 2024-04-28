<?php
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
function elektro($opt, $datum)
{

    $rTypePrevzeta = "32.0.4.1.1.2.12.0.0.0.0.0.0.0.0.3.72.0";
    $rTypeOddana = "32.0.4.1.19.2.12.0.0.0.0.0.0.0.0.3.72.0";
    $rType = "";

    $rType = $opt ? $rTypeOddana : $rTypePrevzeta;

    $stK = 0;
    $stZ = 0;

    $datumN = date('Y-m-d', strtotime($datum . ' + 2 day'));
    //echo $datumN;
    $url = "https://api-test.informatika.si/mojelektro/v1/meter-readings?usagePoint=".$_ENV['USAGE_POINT']."&startTime=" . urlencode($datum) . "&endTime=" . urlencode($datumN) . "&option=ReadingType%3D" . $rType;
    //echo $url;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-API-TOKEN:" . $_ENV['X_API_TOKEN'],
        'accept: application/json'
    ]);

    $resp = curl_exec($ch);             //curl odgovor api klica
    file_put_contents('mojE', $resp);   //shrani curl odgovor v datoteko
    $resp = file_get_contents('mojE');  //prebere curl odgovor iz datoteke (uporaba pri testiranju, za zmanjšanje število klicev)

    if ($e = curl_error($ch)) {
        echo $e;
    } else {
        $decoded = json_decode($resp, true); //true spremeni object v array
        $data = $decoded['intervalBlocks'][0]['intervalReadings']; // za lažje branje kode v naslednjih vrsticah

        foreach ($data as $x => $y) {
            if (str_contains($y['timestamp'], $datum)) { //preveri če je iskan datum v odgovoru
                $stZ = $y['value'];

                $check = str_contains($data[$x + 1]['timestamp'], date('Y-m-d', strtotime($datum . ' + 1 day')));

                $key = $check ? $x + 1 : $x + 2;
                $stK = $data[$key]['value'];
                break;
            }
        }


        return $stK - $stZ;
    }


}