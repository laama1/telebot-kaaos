<?php
//namespace plugins;

class Krnytsoi {

    private $nytsoiurl = 'http://kaaosradio.fi/nytsoi.txt';

    /**
     * 0 = telegram
     * 1 = discord
     * @var int
     */
    private $which_platform = 0;

    public function __construct(int $which_platform = 0) {
        $this->which_platform = $which_platform;
    }

    public function handle($args = null): string {
        if ($args[0]) {
            $command = $args[0];
        }
        if ($args[1]) {
            $param1 = $args[1];
        }
        $data = '';

        if ($command == '/krnytsoi') {
            $this->krnytsoi($param1);
        }

        return $data;
    }

    private function krnytsoi($param = '') {

    }
}