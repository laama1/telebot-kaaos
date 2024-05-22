<?php
namespace Telebot\Plugins;

class Krnytsoi extends Template {

    private $nytsoiurl = 'http://kaaosradio.fi/nytsoi.txt';
    private $update_krnytsoi_url = 'https://kaaosradio.fi/nytsoi_api/nytsoi_api.php?';
    protected $logfile = '';

    /**
     * 0 = telegram
     * 1 = discord
     * @var int
     */
    protected $which_platform = 0;

    public function __construct(int $which_platform = 0) {
        $this->logfile = __DIR__.'/../logs/'.__CLASS__.'.log';
        $this->which_platform = $which_platform;
        include __DIR__.'/../config.php';
        $this->update_krnytsoi_url .= $nytsoiapi_param .'='. $nytsoiapi_arg . '&';
    }

    public function handle(array $args = []): string {
        if ($args[0]) {
            $command = $args[0];
        }
        if ($args[1]) {
            $params = implode(' ', array_splice($args, 1));
        }
        $data = '';

        if ($command == '/krnytsoi') {
            $this->krnytsoi($params);
        }
        return $data;
    }

    private function krnytsoi($param = '') {
        if ($param == '') return;
        $this->log(__LINE__. ' ' . $param);
        $param = urlencode($param);
        $url = $this->update_krnytsoi_url . "text-audio=1&text-chat=1&song=$param";
        file_get_contents($url);
    }
}