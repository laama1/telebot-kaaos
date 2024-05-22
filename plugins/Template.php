<?php
namespace Telebot\Plugins;
class Template {

    private $nytsoiurl = 'http://kaaosradio.fi/nytsoi.txt';
    protected $logfile = '';
    /**
     * 0 = telegram
     * 1 = discord
     * @var int
     */
    protected $which_platform = 0;
    protected $logenabled = 0;

    public function __construct($which_platform = 0) {
        $this->which_platform = $which_platform;
        $this->logfile = __DIR__.'/../logs/'.__CLASS__.'.log';
    }

    public function handle(array $args = []): string {
        if ($args[0]) {
            $command = $args[0];
        }
        if ($args[1]) {
            $params = implode(' ', array_splice($args, 1));
        }
        $data = '';
        return $data;
    }

    protected function log(string $text) {
		if ($this->logenabled) {
			file_put_contents($this->logfile, date('Y-m-d H:i:s').':'.__CLASS__.':'. $text . ', IP: ' . $this->get_ip() . PHP_EOL, FILE_APPEND);
		}
	}

	protected function get_ip() : string {
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