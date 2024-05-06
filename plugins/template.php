<?php

class Template {

    private $nytsoiurl = 'http://kaaosradio.fi/nytsoi.txt';

    /**
     * 0 = telegram
     * 1 = discord
     * @var int
     */
    private $which_platform = 0;

    public function __construct($which_platform = 0) {
        $this->which_platform = $which_platform;
    }

    public function handle($args = []): string {
        if ($args[0]) {
            $command = $args[0];
        }
        if ($args[1]) {
            $param1 = $args[1];
        }
        $data = '';

        return $data;
    }
}