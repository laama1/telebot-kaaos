<?php
$listenurl = 'https://kaaosradio.fi:8001';
$pinned_msg_id = -1;
$chat_msg = '';
$return_msg = [];
if (!isset($_GET)) return;

include dirname(__FILE__).'/config.php';

function pinMsg($channel_id, $path, $msgId) {
    $url = $path . '/pinChatMessage?chat_id=' . $channel_id . '&message_id=' . $msgId;
    return doRequest($url);
}

function unPinMsg($channel_id, $path, $msgId) {
    $url = $path . '/unpingChatMessage?chat_id=' . $channel_id . '&message_id=' . $msgId;
    return doRequest($url);
}

function doRequest($url) {
    $response_json = null;
    try {
        $response = file_get_contents($url);
        $response_json = json_decode($response, false);

        // log response to file
        file_put_contents(__DIR__.'/logs/responselog.txt', 'Response ok: ' . $response);

    } catch (Exception $e) {
        file_put_contents(__DIR__. '/logs/response_error.txt', 'Response error: ' . $e->getMessage(), FILE_APPEND);
        exit;
    }
    return $response_json;
}

if (isset($_GET['test'])) {
    $returnMsg = ['testjson' => 'testvalue'];
} elseif (isset($_GET['nytsoi'])) {
    $data = $_GET['nytsoi'];
    $postdata = '<b>np:</b> ' . $data;
    $chat_msg = urlencode($postdata);
    $url = $path . '/sendmessage?chat_id=' . $channels['kaaosradio'] . '&parse_mode=html&text=' . $chat_msg;
    $response_json = doRequest($url);

    if (isset($response_json->result)) {
        $pinned_msg_id = $response_json->result->message_id;
        pinMsg($channels['kaaosradio'], $path, $pinned_msg_id);
        $returnMsg = [ 'result' => 'ok',
                        'pinned_msg_id' => $pinned_msg_id 
                    ];
    }

} elseif (isset($_GET['viesti'])) {
    $postdata = $_GET['viesti'];
} elseif (isset($_GET['nytsoivideo'])) {
    $data = $_GET['nytsoivideo'];
    $postdata = '<b>Videostream!</b> ' . $data . ' https://videostream.kaaosradio.fi';
    $chat_msg = urlencode($postdata);
    $url = $path . '/sendmessage?chat_id=' . $channels['kaaosradio'] . '&parse_mode=html&text=' . $chat_msg;
    $response_json = doRequest($url);

    if (isset($response_json->result)) {
        $pinned_msg_id = $response_json->result->message_id;
        pinMsg($channels['kaaosradio'], $path, $pinned_msg_id);
        $returnMsg = [ 'pinned_msg_id' => $pinned_msg_id];
    }

} elseif (isset($_GET['unpin'])) {
    $data = $_GET['unpin'];
    $url = $path . '/unpinChatMessage?chat_id=' . $channels['kaaosradio'] . '&message_id=' . $data;
    $response_json = doRequest($url);

} else {
    return;
}

// print return message
echo json_encode($returnMsg);