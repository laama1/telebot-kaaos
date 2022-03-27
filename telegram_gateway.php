<?php
if (!isset($_GET)) return;

if (isset($_GET['nytsoi'])) {
    $data = utf8_decode($_GET['nytsoi']);
    $postdata = '<b>Nytsoi päivitetty!</b> '.$data. ' '. $this->listenurl.'/stream';
} elseif (isset($_GET['viesti'])) {
    $postdata = utf8_decode($_GET['viesti']);
} elseif (isset($_GET['nytsoivideo'])) {
    $data = $_GET['nytsoivideo'];
    $postdata = '<b>Videostream!</b> '.$data. ' https://videostream.kaaosradio.fi';
}

include dirname(__FILE__).'/config.php';

$chat = urlencode($postdata);
$url = $path.'/sendmessage?chat_id='.$channels['kaaosradio'].'&parse_mode=html&text='.$chat;
$response = file_get_contents($url);

?>