<?php

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

$konec = 0;

$datum = isset($_POST['date']) ? $_POST['date'] : "2023-09-19";
function elektro($opt, $datum)
{
    echo "Datum ki gre v funkcijo" . $datum;
    $servername = "localhost";
    $username = "root";
    $password = "";

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, "diplomska");

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    echo "Connected successfully";
    $timestamp = 0;


    $rTypes = [
        "32.0.4.1.1.2.12.0.0.0.0.0.0.0.0.3.72.0",//prevzeta
        "32.0.4.1.19.2.12.0.0.0.0.0.0.0.0.3.72.0",//oddana
        "32.0.2.4.1.2.37.0.0.0.0.0.0.0.0.3.38.0"//15prevzeta
    ];
    $rType = "";
    $dTypes = [
        "elprejeto",
        "eloddano",
        "el15prejeto"
    ];
    $dType = "";

    $rType = $rTypes[$opt];
    $dType = $dTypes[$opt];
    echo $dType;

    $datumN = date('Y-m-d', strtotime($datum . ' + 1 month'));
    echo $datumN;
    $url = "https://api.informatika.si/mojelektro/v1/meter-readings?usagePoint=".$_ENV['USAGE_POINT']."&startTime=" . urlencode($datum) . "&endTime=" . urlencode($datumN) . "&option=ReadingType%3D" . $rType;
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

            $stZ = $y['value'];
            $timestamp = substr($y['timestamp'], 0, -6);
            $timestamp = str_replace('T', ' ', $timestamp);
            echo $timestamp;

            $sql = "INSERT INTO " . $dType . " VALUES ('" . $timestamp . "', '" . $stZ . "')";

            if ($conn->query($sql) === TRUE) {
                echo "<br>New record created successfully <br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
    echo $datumN;
    echo gettype($datumN);

    if (!str_contains($datumN, '2024-05-01')) {
        elektro($opt,$datumN);
    } else {
        echo "nekliči";
    }

}

function solaredge($datum)
{

    $servername = "localhost";
    $username = "root";
    $password = "";

    $conn = mysqli_connect($servername, $username, $password, "diplomska");

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    echo "Connected successfully";

    $api_key = $_ENV['SOLAREDGE_API_KEY'];
    $site_id = $_ENV['SITE_ID'];

    $datumN = date('Y-m-d', strtotime($datum . ' + 1 month'));
    echo $datumN;

    $url = "https://monitoringapi.solaredge.com/site/" . $site_id . "/energy?timeUnit=DAY&endDate=" . urlencode($datumN) . "&startDate=" . urlencode($datum) . "&api_key=" . $api_key;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //ne preveri SSL certifikata
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $resp = curl_exec($ch);                //curl odgovor api klica
    file_put_contents('data', $resp);   //shrani curl odgovor v datoteko
    $resp = file_get_contents('data');

    echo "Na dan " . $datum . " je bilo proizvedeno: ";

    if ($e = curl_error($ch)) {
        echo $e;
    } else {
        $decoded = json_decode($resp, true); //true spremeni object v array
        //print_r($decoded);
        foreach ($decoded['energy']['values'] as $x => $y) {
            foreach ($y as $var => $val) {
                //print_r($y);
                if ($var == 'value') {

                    $sql = "INSERT INTO solaredge VALUES ('" . $y['date'] . "', '" . $val . "')";
                    echo "Vneseno" . $y['date'] . " in " . $val;
                    if ($conn->query($sql) === TRUE) {
                        echo "<br>New record created successfully <br>";
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }
                }

            }
        }
    }

    if (!str_contains($datumN, '2024-04-19')) {
        solaredge($datumN);
    } else {
        echo "nekliči";
    }

}


/* elektro(0,$datum); */
/* elektro(1,$datum); */
/* elektro(2,$datum); */
/* solaredge($datum); */




