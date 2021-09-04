<?php
$postdata = file_get_contents('php://input');
file_put_contents(dirname(__FILE__).'/logs/twiitch.log', print_r($_REQUEST, true), FILE_APPEND);
file_put_contents(dirname(__FILE__).'/logs/twiitch.log', $postdata, FILE_APPEND);
?>