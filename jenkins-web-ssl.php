<?php

set_time_limit(0);
ini_set('max_execution_time', 0);
const QUEUE_POLL_INTERVAL = 3;
const JOB_POLL_INTERVAL = 20;
const OVERALL_TIMEOUT = 3600; // 1 hour
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

$auth_token = "usernamejenkins:tokenjenkins"; //'user:token_code';
$jenkins_uri = "10.10.0.11:8443"; //'ip:port';
$job_name = "Job-or-pipeline"; //'Pipeline-shell';
$build_token = $_SESSION['token'];

$parameter1 = $_GET["parameter1"]; // 'get';
$parameter2 = $_GET["parameter2"]; // 'get';
$parameter3 = $_GET["parameter3"]; // 'get';
$parameter4 = $_GET["parameter4"]; // 'get';
$parameter5 = "static"; // 'static parameter';
$ipclient = $_SERVER['REMOTE_ADDR'];

$_SESSION['parameter4'] = $_GET['parameter4'];
$_SESSION['operazione'] = $parameter5;
$_SESSION['instance-name'] = $_GET["parameter2"];

echo "<br><div class='banner'>";
echo $_SESSION['dominio'];
echo "   >";
echo " Esecuzione <b>$parameter5</b> in corso: ";
echo "<b>";
echo $_SESSION['appname'];
echo "</b>";
echo " su ISTANZA: ";
echo "<b>";
echo $_SESSION['parameter4'];
echo "</b>";
echo "</div>";
echo "<img align='center' src='../img/loader.gif' width='30px' height='30px'>";
$parameter3 = str_replace ( ' ', '%20', $parameter3);

echo "<div class='jenkins'>";
$start_build_url = sprintf('https://%s@%s/job/%s/buildWithParameters?token=%s&parameter1=%s&parameter2=%s&parameter3=%s&parameter4=%s&parameter5=%s&ipclient=%s', $auth_token, $jenkins_uri, $job_name, $build_token, urlencode($parameter1), $parameter2, urlencode($parameter3), $parameter4, $parameter5, $ipclient );
$start_build_response = file_get_contents($start_build_url, false, stream_context_create($arrContextOptions));
$http_response_header["Location"] = substr($http_response_header[4],10);
//var_dump($start_build_url);

$parsed_location = parse_url($http_response_header["Location"]);
if (!(isset($parsed_location["path"]) && strpos($parsed_location["path"], 'queue'))){
    die("Compilazione non inserita in coda");
}
$queue_id = $parsed_location["path"];
$job_info_url = sprintf('https://%s@%s%sapi/json', $auth_token, $jenkins_uri, $queue_id);
$elasped_time = 0;

// Nascondi le credenziali dall'output
$masked_url = preg_replace('/\/\/(.*):(.*)@/', '//*:*@', $start_build_url);

output(date("c")." Compilazione $job_name aggiunta in coda: $masked_url </br>");

while (true) {
    $job_info_response = file_get_contents($job_info_url, false, stream_context_create($arrContextOptions));
    if ($jqe = json_decode($job_info_response, true)){

        if (isset($jqe['executable']) && isset($jqe['executable']['number'])) {
            $job_id = $jqe['executable']['number'];
                        break;
        } else {
            $task = isset($jqe['task']) && isset($jqe['task']['name'])?$jqe['task']['name']:'';
            output("Compilazione non ancora avviata: $task </br>");
            sleep(QUEUE_POLL_INTERVAL);
            $elasped_time += QUEUE_POLL_INTERVAL;
        }
        if (($elasped_time % (QUEUE_POLL_INTERVAL * 10)) == 0) {
            output(date("c").": Compilazione $job_name ancora in coda $queue_id </br>");
                }
    } else {
        output("Risposta json sbagliata");
    }
}

$job_url = sprintf('https://%s@%s/job/%s/%s/api/json', $auth_token, $jenkins_uri, $job_name, $job_id);
// Nascondi le credenziali dall'output
$masked_url = preg_replace('/\/\/(.*):(.*)@/', '//*:*@', $job_url);

$start_epoch = time();
while (True){
        echo "-----------------------------------------";
        echo "<br/>";
    output(date("c").": Compilazione avviata, URL: $masked_url </br>");
    $job_response = file_get_contents($job_url, false, stream_context_create($arrContextOptions));

    $job_json = json_decode($job_response, true);

    if ($job_json['result'] == 'SUCCESS') {
        # Do success steps
        output(date("c").": Job: $job_name, Status: ".$job_json['result']." </br>");
        break;
    } else if ($job_json['result']  == 'FAILURE') {
        # Do failure steps
        output(date("c").": Job: $job_name, Status: ".$job_json['result']." </br>");
        break;
    } else if ($job_json['result']  == 'ABORTED') {
        # Do aborted steps
        output(date("c").": Job: $job_name, Status: ".$job_json['result']." </br>");
        break;
    } else {
        output(date("c").": Job: $job_name, Status: ".$job_json['result']."Nuovo check stato Job tra ".JOB_POLL_INTERVAL." secondi </br>");
    }

    $cur_epoch = time();
    if (($cur_epoch - $start_epoch) > OVERALL_TIMEOUT){
        output(date("c").": No status before timeout of ".OVERALL_TIMEOUT." secs </br>");
        die();
    }
    sleep(JOB_POLL_INTERVAL);
}
ob_end_flush();
function output($str) {
    ob_start();
    echo $str;
    ob_end_flush();
    ob_flush();
    flush();
}
echo "</div>";
?>
