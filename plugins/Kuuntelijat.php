<?php
namespace Telebot\Plugins;
class Kuuntelijat extends Template {

    private $kuuntelijat_address = 'http://kaaosradio.fi/kuuntelijat/kuuntelijat_api.php';

    public function handle(array $args = []) : string {
        $data = '';
        if ($data = json_decode(file_get_contents($this->kuuntelijat_address))) {
            if ($this->which_platform == 0) {
                $data = '<b>/// '.$data->mount.' Kuuntelijat:</b> '.$data->listeners.', <b>Max kuuntelijat:</b> '.$data->peak_listeners;
            } elseif ($this->which_platform == 1) {
                $data = '**/// '.$data->mount.' Kuuntelijat:** '.$data->listeners.', **Max kuuntelijat:** '.$data->peak_listeners;
            }
        }
        return $data;
    }
}


?>