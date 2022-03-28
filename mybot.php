<?php
require_once('plugins/weather.php');
require_once('plugins/np.php');
require_once('plugins/kuuntelijat.php');
require_once('plugins/nytsoi.php');
require_once('plugins/help.php');
require_once('plugins/seuraavat.php');

class TelegramApi {

/**
 * Telegram bot.
 * Answers to telegram !commands.
 * Bot Must be configured in telegram first.
 * 
 * @author LAama1
 * @date 24.11.2020, 27.3.2022
 * 
 */

	private $path = '';				// telegram webhook url
	private $logfile;				// text log file
	private $logenabled = 0;		// log to file enabled or not
	private $channels = array();	// telegram channels / their id's
	private $chatId = '';			// where the message came from
	private $tags = '';				// id-tags for stream
	private $listenurl = 'https://kaaosradio.fi:8001/';		// kaaosradio icecast server address

	private $commands = array();

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
		
		$this->commands = [
			'!np' => new Np(),
			'!nytsoi' => new Nytsoi(),
			'!kuuntelijat' => new Kuuntelijat(),
			'!seuraavat' => new Seuraavat(null),
			'!seuraava' => new Seuraavat(1),
			'!s' => new Weather(),
			'!help' => new Help(),
		];
		
		
		$tags = '';
		$data = '';

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
		$args = explode(' ', $data);
		$command = array_shift($args);
		if (isset($this->commands[$command])) {
			$this->log(__LINE__.' Command found! '.$command);
			$this->log('Args: '.print_r($args, true));
			$tags = $this->commands[$command]->handle($args);
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