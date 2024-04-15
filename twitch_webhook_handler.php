<?php

function logita(string $data) {
    file_put_contents(__DIR__.'/logs/twiitch.log', date('Y-m-d H:i:s'). ' '.$data . PHP_EOL, FILE_APPEND);
}

$postdata = file_get_contents('php://input');
logita('REQUEST: '.print_r($_REQUEST, true));
logita('POST data: '.print_r($postdata, true));

foreach (getallheaders() as $name => $value) {
    logita("$name: $value");
}

/*
LAamanni2
https://id.twitch.tv/oauth2/authorize?response_type=token&client_id=tzo55w9b8nc3e13d5rhcpu7ykj7rcl&redirect_uri=https://8-b.fi/~laama/telebot-kaaos/twitch_webhook_handler.php&scope=chat%3Aread+chat%3Aedit
oauth: access token: 9wjyl60ean3pvpbk4zzbkgruaeue7j

*/
?>