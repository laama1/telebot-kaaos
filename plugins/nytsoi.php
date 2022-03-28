<?php

class Nytsoi {

    private $nytsoiurl = 'http://kaaosradio.fi/nytsoi.txt';

    public function __construct() {
        
    }

    public function handle($args) : ?string {
        if ($data = file_get_contents($this->nytsoiurl)) {
            return "<b>/// Nytsoi:</b> ".$data;
        }
    }
}

?>