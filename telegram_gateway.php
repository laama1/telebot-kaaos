<?php
$listenurl = 'https://kaaosradio.fi:8001';
$pinned_msg_id = 1;

if (!isset($_GET)) return;

if (isset($_GET['nytsoi'])) {
    $data = $_GET['nytsoi'];
    $postdata = '<b>Nytsoi päivitetty!</b> '.$data;
} elseif (isset($_GET['viesti'])) {
    $postdata = $_GET['viesti'];
} elseif (isset($_GET['nytsoivideo'])) {
    $data = $_GET['nytsoivideo'];
    $postdata = '<b>Videostream!</b> '.$data. ' https://videostream.kaaosradio.fi';
} else {
    return;
}

$chat = urlencode($postdata);

include dirname(__FILE__).'/config.php';
$url = $path.'/sendmessage?chat_id='.$channels['kaaosradio'].'&parse_mode=html&text='.$chat;
$response = file_get_contents($url);
try {
    $response_json = json_decode($response, false);
    if (isset($response->result->message_id)) {
        $pinned_msg_id = $response->result->message_id;
    }
} catch (Exception $e) {
    file_put_contents(__DIR__. '/logs/response_error.txt', 'Response error: ' . $e->getMessage(), FILE_APPEND);
}

file_put_contents(__DIR__.'/logs/responselog.txt', "telegram_gateway ($pinned_msg_id): " . print_r($response, true), FILE_APPEND);
