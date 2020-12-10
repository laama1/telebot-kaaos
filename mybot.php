<?php
require_once('weather.php');

class MyBot {

/**
 * Telegram bot. Sends messages received via HTTP-GET to telegram.
 * @author LAama1
 * @date 24.11.2020
 * 
 */

	private $path = '';				// telegram webhook url
	private $logfile;				// text log file
	private $logenabled = 1;		// log to file enabled or not
	private $channels = array();	// telegram channels / their id's
	private $chatId = '';			// where the message came from
	private $tags = '';				// id-tags for stream
	private $listenurl = 'http://kaaosradio.fi:8000/';		// kaaosradio icecast server address

	public function __construct() {
		$this->logfile = dirname(__FILE__). '/logs/debug.txt';
		$input = file_get_contents("php://input");
		$update = json_decode($input, TRUE);
		$this->log('--------------------');
		#$this->log($input . PHP_EOL);
		include dirname(__FILE__).'/config.php';
		$this->path = $path;
		$this->channels = $channels;
		$this->logenabled = $logenabled;
		$tags = '';

		if (isset($_GET) && isset($_GET['nytsoi'])) {
			$data = $_GET['nytsoi'];
			$this->msg_to_kaaos('<b>Nytsoi päivitetty!</b> '.$data. ' http://stream.kaaosradio.fi:8000/stream');
			$this->log(__LINE__.' Nytsoi: '.$data);
			return;
		} elseif (isset($_GET) && isset($_GET['viesti'])) {
			$data = $_GET['viesti'];
			$this->log(__LINE__.' Viesti: '.$data);
			//$this->msg_to_bot_test(utf8_decode($data));
			$this->msg_to_kaaos(utf8_decode($data));  
			return;
		}
		if (isset($update['message'])) {
			// Private message
			$this->chatId = $update["message"]["chat"]["id"];
			$data = $update["message"]["text"];
		} elseif (isset($update['channel_post'])) {
			// Channel message
			$data = $update['channel_post']['text'];
			$this->chatId = $update['channel_post']['chat']['id'];
		}

		if (strpos($data, "!np stream2") === 0) {
			$tags = file_get_contents('http://kaaosradio.fi/npfile_stream2_tags');
			$tags .= "\n".$this->listenurl.'stream2';

		} elseif (strpos($data, "!np chill") === 0) {
			$tags = "<b>np:</b> ".file_get_contents('http://kaaosradio.fi/npfile_chill_tags');
			$tags .= "\n".$this->listenurl.'chill';

		} elseif (strpos($data, "!np chip") === 0) {
			$tags = "<b>np:</b> ".file_get_contents('http://kaaosradio.fi/npfile_chip_tags');
			$tags .= "\n".$this->listenurl.'chip';

		} elseif (strpos($data, "!np") === 0) {
			$tags = "<b>np:</b> ".file_get_contents('http://kaaosradio.fi/npfile_stream2_tags');
			//$tags .= "<br>".$this->listenurl.'stream2';

		} elseif (strpos($data, "!nytsoi") === 0) {
			$tags = "<b>Nytsoi:</b> ".file_get_contents('http://kaaosradio.fi/nytsoi.txt');
			
		} elseif (strpos($data, '!kuuntelijat') === 0 || strpos($data, '!kuulijat') === 0) {
			$this->get_kuuntelijat();
			return;
		} elseif (strpos($data, '!s') === 0) {
			#$this->log(__LINE__.' Still here7...');
			$w = new Weather($data);
			#$this->log(__LINE__.' Still here7.1..');
			$tags = $w->getMessage();
			$this->log(__LINE__.' Still here7.2... tags: '.$tags);
		}
		if ($tags != '') {
			$this->msg_to_priv($tags, $this->chatId);
		}
	}

	private function msg_to_priv($text, $chatid) {
		$chat = urlencode($text);
		$response = file_get_contents($this->path."/sendmessage?chat_id=".$chatid.'&text='.$chat."&parse_mode=html");
		$this->log('Text: '.$text.', Chatid: '.$chatid.', Response: '.$response);
	}

	private function msg_to_kaaos($text) {
		$chat = urlencode($text);
		//$this->log(__LINE__.' chat: '.$chat);
		$url = $this->path.'/sendmessage?chat_id='.$this->channels['kaaosradio'].'&parse_mode=html&text='.$chat;
		$this->log(__LINE__.' url: '.$url);
		$response = file_get_contents($url);
		$this->log(__LINE__.' response: ');
		$this->log($response);
	}

	private function msg_to_bot_test($text) {
		$chat = urlencode($text);
		//$this->log(__LINE__.' chat: '.$chat);
		$url = $this->path."/sendmessage?chat_id=".$this->channels['kaaos-bot-testing']."&parse_mode=html&text=".$chat;
		$this->log(__LINE__.' url: '.$url);
		$response = file_get_contents($url);
		$this->log(__LINE__.' response: ');
		$this->log($response);
	}

	private function log($text) {
		if ($this->logenabled) {
			file_put_contents($this->logfile, date('Y-m-d H:i:s').': '.$text . PHP_EOL, FILE_APPEND);
		}
	}

	private function get_kuuntelijat() {
		$data = file_get_contents('http://kaaosradio.fi/kuuntelijat/kuuntelijat_api.php');
		$this->msg_to_kaaos($data);
	}
}

$botten = new MyBot();
?>