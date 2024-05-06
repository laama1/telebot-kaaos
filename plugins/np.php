<?php

class Np {
    private $npfile_address = 'https://kaaosradio.fi/npfile_';
    private $listen_address = 'https://kaaosradio.fi:8001/';

    public function __construct() {

    }
    public function handle($args) : string {
        $tags = '';
        if (!$args[1]) {
            $args[1] = 'stream2';
        }
        if ($tags = file_get_contents($this->npfile_address.$args[1].'_tags')) {
            if ($args[1] != 'stream2') $tags .= "\n".$this->listen_address.$args[1];
        }
        return $tags;
    }
}

?>