<?php
namespace Telebot\Plugins;

class Krnytsoi extends Template {

    private $nytsoiurl = 'http://kaaosradio.fi/nytsoi.txt';
    private $update_krnytsoi_url = 'https://kaaosradio.fi/nytsoi_api/nytsoi_api.php?';
    protected $logenabled = 1;
    /**
     * 0 = telegram
     * 1 = discord
     * @var int
     */


    public function handle(array $args = []): string {
        include __DIR__.'/../config.php';
        $this->update_krnytsoi_url .= $nytsoiapi_param .'='. $nytsoiapi_arg . '&';
        if ($args[0]) {
            $command = $args[0];
            $this->log(__LINE__. ' command: ' . $command);
        }
        if ($args[1]) {
            $params = implode(' ', array_splice($args, 1));
        }
        $this->log(__LINE__. ' params: ' . $params);
        $data = '';

        switch ($command) {
            case '/krnytsoi':
                $this->krnytsoi($params, 'text-audio');
                break;
            case '/krnytsoivideo':
                $this->krnytsoi($params, 'text-video');
                break;
            case '/krnytsoivideotwitch':
                $this->krnytsoi($params, 'text-twitch');
                break;
            default:
                # code...
                break;
        }

        return $data;
    }

    private function krnytsoi($param = '', $extra = '') {
        if ($param == '') return;
        $this->log(__LINE__. ' param: ' . $param .', extra: ' . $extra);
        $param = urlencode($param);
        $url = $this->update_krnytsoi_url . $extra . "=1&text-chat=1&song=$param";
        file_get_contents($url);
    }
}