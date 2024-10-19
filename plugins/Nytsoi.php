<?php
namespace Telebot\Plugins;
class Nytsoi extends Template {

    private $nytsoiurl = 'http://kaaosradio.fi/nytsoi.txt';

    public function handle(array $args = []) : string {
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
