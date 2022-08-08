<?php

class Kuuntelijat {

    private $kuuntelijat_address = 'http://kaaosradio.fi/kuuntelijat/kuuntelijat_api.php';
    private $which_platform = 0;

    public function __construct($which_platform = 0) {
        $this->which_platform = $which_platform;
        
    }

    public function handle($args = null) : string {
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