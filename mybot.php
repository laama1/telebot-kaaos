<?php
require_once('weather.php');

class TelegramApi {

/**
 * Telegram bot. Sends messages received via HTTP-GET to telegram. Like a gateway
 * Answers to telegram !commands.
 * Supported commands:
 * !nytsoi (now playing in stream)
 * !kuuntelijat (listeners)
 * !np chill (song info for chill stream)
 * !np
 * !s <city> (for weather)
 * 
 * @author LAama1
 * @date 24.11.2020
 * 
 */

	private $path = '';				// telegram webhook url
	private $logfile;				// text log file
	private $logenabled = 0;		// log to file enabled or not
	private $channels = array();	// telegram channels / their id's
	private $chatId = '';			// where the message came from
	private $tags = '';				// id-tags for stream
	private $listenurl = 'https://kaaosradio.fi:8001/';		// kaaosradio icecast server address

	public function __construct() {
		$this->logfile = dirname(__FILE__). '/logs/TelegramApi_debug.txt';
		$input = file_get_contents("php://input");
		$update = json_decode($input, TRUE);
		$this->log('--------------------');
		#$this->log($input . PHP_EOL);
		include dirname(__FILE__).'/config.php';
		$this->path = $path;
		$this->channels = $channels;
		$this->logenabled = $logenabled;
		$tags = '';
		$data = '';
		if (isset($_GET) && isset($_GET['nytsoi'])) {
			$data = utf8_decode($_GET['nytsoi']);
			#$this->log(__LINE__.' Nytsoi: '.$data);
			$this->msg_to_kaaos('<b>Nytsoi päivitetty!</b> '.$data. ' '. $this->listenurl.'/stream');
			return;
		} elseif (isset($_GET) && isset($_GET['viesti'])) {
			$data = $_GET['viesti'];
			#$this->log(__LINE__.' Viesti: '.$data);
			//$this->msg_to_bot_test(utf8_decode($data));
			$this->msg_to_kaaos(utf8_decode($data));
			return;
		} elseif (isset($_GET) && isset($_GET['nytsoivideo'])) {
			$data = $_GET['nytsoivideo'];
			#$this->log(__LINE__.' Nytsoivideo: '.$data);
			$this->msg_to_kaaos('<b>Videostream!</b> '.$data. ' https://videostream.kaaosradio.fi');
			return;
		}
		if (isset($update['message'])) {
			// Private message
			$data = $update["message"]["text"];
			$this->chatId = $update["message"]["chat"]["id"];
		} elseif (isset($update['channel_post'])) {
			// Channel message
			$data = $update['channel_post']['text'];
			$this->chatId = $update['channel_post']['chat']['id'];
		}
		if ($data == '') return;

		if (strpos($data, "!np stream2") !== false) {
			$tags = file_get_contents('http://kaaosradio.fi/npfile_stream2_tags');
			$tags .= "\n".$this->listenurl.'stream2';

		} elseif (strpos($data, "!np chill") !== false) {
			$tags = "<b>np:</b> ".file_get_contents('http://kaaosradio.fi/npfile_chill_tags');
			$tags .= "\n".$this->listenurl.'chill';

		} elseif (strpos($data, "!np chip") !== false) {
			$tags = "<b>np:</b> ".file_get_contents('http://kaaosradio.fi/npfile_chip_tags');
			$tags .= "\n".$this->listenurl.'chip';

		} elseif (strpos($data, "!np") !== false) {
			$tags = "<b>np:</b> ".file_get_contents('http://kaaosradio.fi/npfile_stream2_tags');
			//$tags .= "<br>".$this->listenurl.'stream2';

		} elseif (strpos($data, "!nytsoi") !== false) {
			$tags = "<b>Nytsoi:</b> ".file_get_contents('http://kaaosradio.fi/nytsoi.txt');
			
		} elseif (strpos($data, '!kuuntelijat') !== false || strpos($data, '!kuulijat') !== false) {
			$this->get_kuuntelijat();
			return;
		} elseif (strpos($data, '!s') !== false) {
			$w = new Weather($data);
			$tags = $w->getMessage();
		}
		if ($tags != '') {
			$this->msg_to_priv($tags, $this->chatId);
		}
	}

	private function msg_to_priv($text, $chatid) {
		$chat = urlencode($text);
		$response = file_get_contents($this->path."/sendmessage?chat_id=".$chatid.'&text='.$chat."&parse_mode=html");
		$this->log(__LINE__.' Text: '.$text.', chat: '.$chat.', Chatid: '.$chatid.', Response: '.$response);
	}

	private function msg_to_kaaos($text) {
		$chat = urlencode($text);
		$url = $this->path.'/sendmessage?chat_id='.$this->channels['kaaosradio'].'&parse_mode=html&text='.$chat;
		$response = file_get_contents($url);
		$this->log(__LINE__.' response from telegram api (in next line): ');
		$this->log($response);
		$data = isset($http_response_header) ? $http_response_header : '';
		$this->log('Response header:'. print_r($data));
	}

	private function msg_to_bot_test($text) {
		$chat = urlencode($text);
		//$this->log(__LINE__.' chat: '.$chat);
		$url = $this->path."/sendmessage?chat_id=".$this->channels['kaaos-bot-testing']."&parse_mode=html&text=".$chat;
		$this->log(__LINE__.' Text: '.$text.', chat: '.$chat.', url: '.$url);
		$response = file_get_contents($url);
		$this->log(__LINE__.' response: ');
		$this->log($response);
	}

	private function log($text) {
		if ($this->logenabled) {
			file_put_contents($this->logfile, date('Y-m-d H:i:s').': IP: '.$this->get_ip().', '.$text . PHP_EOL, FILE_APPEND);
		}
	}

	private function get_kuuntelijat() {
		$data = file_get_contents('http://kaaosradio.fi/kuuntelijat/kuuntelijat_api.php');
		$this->msg_to_kaaos($data);
	}

	private function get_ip() : string {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))	{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}

$botten = new TelegramApi();
?>