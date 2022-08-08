<?php

class Nytsoi {

    private $nytsoiurl = 'http://kaaosradio.fi/nytsoi.txt';
    private $which_platform = 0;

    public function __construct($which_platform = 0) {
        $this->which_platform = $which_platform;
    }

    public function handle($args = null) : string {
        $data = '';
        if ($data = file_get_contents($this->nytsoiurl)) {
            if ($this->which_platform == 0) {
                // telegram
                $data = "<b>/// Nytsoi:</b> ".$data;
            } else if ($this->which_platform == 1) {
                // discord
                $data = "**/// Nytsoi:** ".$data;
            }
            
        }
        return $data;
    }
}

?>