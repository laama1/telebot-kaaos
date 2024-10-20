<?php
require_once(__DIR__ . '/plugins/Template.php');
require_once(__DIR__ . '/plugins/Seuraavat.php');
require_once(__DIR__ . '/plugins/Weather.php');
require_once(__DIR__ . '/plugins/Np.php');
require_once(__DIR__ . '/plugins/Kuuntelijat.php');
require_once(__DIR__ . '/plugins/Nytsoi.php');
require_once(__DIR__ . '/plugins/Help.php');
require_once(__DIR__ . '/plugins/Sober_curious.php');
require_once(__DIR__ . '/plugins/Krnytsoi.php');
require_once(__DIR__ . '/plugins/Tanaan.php');


use Telebot\Plugins\Seuraavat;
use Telebot\Plugins\Weather;
use Telebot\Plugins\Np;
use Telebot\Plugins\Kuuntelijat;
use Telebot\Plugins\Nytsoi;
use Telebot\Plugins\Help;
use Telebot\Plugins\Sober_curious;
use Telebot\Plugins\Krnytsoi;
use Telebot\Plugins\Tanaan;
//use Telebot\Plugins\Template;


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
	private $logenabled = 1;		// log to file enabled or not
	private $channels = array();	// telegram channels / their id's
	private $chatId = '';			// where the message came from
	private $commands = array();

	public function __construct() {
		$this->logfile = dirname(__FILE__). '/logs/TelegramApi_debug.txt';
		$input = file_get_contents("php://input");
		$update = json_decode($input, TRUE);
		include dirname(__FILE__).'/config.php';
		$this->path = $path;
		$this->channels = $channels;
		$this->logenabled = $logenabled;
		
		$this->commands = [
			'!seuraavat' => new Seuraavat(null),
			'!seuraava' => new Seuraavat(1),
			'/seuraavat' => new Seuraavat(null),
			'/seuraava' => new Seuraavat(1),
			'!np' => new Np(),
			'!nytsoi' => new Nytsoi(),
			'!kuuntelijat' => new Kuuntelijat(),
			'!s' => new Weather(),
			'!help' => new Help(),
			'!sober' => new Sober_curious(),
			'/np' => new Np(),
			'/nytsoi' => new Nytsoi(),
			'/krnytsoi' => new Krnytsoi(),
			'/krnytsoivideo' => new Krnytsoi(),
			'/krnytsoivideotwitch' => new Krnytsoi(),
			'/kuuntelijat' => new Kuuntelijat(),
			'/today' => new Tanaan(),
			'!today' => new Tanaan(),
			'/s' => new Weather(),
			'/help' => new Help(),
		];

		$tags = '';
		$data = '';

		if (isset($update['message'])) {
			// Private message
			$data = $update['message']['text'] ?? '';
			$this->chatId = $update["message"]["chat"]["id"];
		} elseif (isset($update['channel_post'])) {
			// Channel message
			$this->log("channel_post");
			$data = $update['channel_post']['text'] ?? '';
			$this->chatId = $update['channel_post']['chat']['id'];
		}
		if ($data == '') return;
		$args = explode(' ', $data);
		//$command = array_shift($args);
		$command = $args[0];
		$command = preg_replace('/\@(.*)/', '', $command);

		if (isset($this->commands[$command])) {
			$this->log(__LINE__.': Command found! '.$command);
			$this->log(__LINE__.': Args: '.print_r($args, true));
			$tags = $this->commands[$command]->handle($args);
			$this->log(__LINE__.': tags: '.$tags);
		} else {
			return;
		}

		if ($tags != '') {
			$this->msg_to_priv($tags, $this->chatId);
		}
	}

	private function msg_to_priv($text, $chatid) {
		$chat = urlencode($text);
		$response = file_get_contents($this->path."/sendmessage?chat_id=".$chatid.'&text='.$chat."&parse_mode=html");
		$this->log(__LINE__.' Text: '.$text.'chat: '.$chat.', Chatid: '.$chatid."\n, Response: ".$response);
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
//			file_put_contents($this->logfile, date('Y-m-d H:i:s').': IP: '.$this->get_ip().', '.$text . PHP_EOL, FILE_APPEND);
			file_put_contents($this->logfile, date('Y-m-d H:i:s').':'.__CLASS__.':'. $text . PHP_EOL, FILE_APPEND);
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
