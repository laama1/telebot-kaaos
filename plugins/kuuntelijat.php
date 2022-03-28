<?php

class Kuuntelijat {

    private $kuuntelijat_address = 'http://kaaosradio.fi/kuuntelijat/kuuntelijat_api.php';

    public function __construct()
    {
        
    }

    public function handle($args) : ?string {
        if ($data = file_get_contents($this->kuuntelijat_address)) {
            return '<b>///</b> '.$data;
        }
    }
}


?>