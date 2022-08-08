<?php

class Seuraavat {
    
    private $tulossa_api_address = 'https://kaaosradio.fi/tulevat/tulevat_api.php?komento=';
    private $onlyone = 0;
    private $which_platform = 0;

    public function __construct($oneormany, $which_platform = 0) {
        if ($oneormany == 1) $this->onlyone = 1;
        $this->which_platform = $which_platform;
    }
    public function handle($args = null) :string {
        $data = '';
        $query = isset($args[1]) ? ' '.$args[1] : '';
        if ($json = file_get_contents($this->tulossa_api_address.'seuraavat'.urlencode($query))) {
            if($newdata = json_decode($json)) {
                foreach ($newdata as $line) {
                    $data .= $this->format_line($line) . "\n";
                    if($this->onlyone == 1) break;
                }
            }
        }
        return $data;
    }

    private function format_line($arr) :string {
        $starttime_formatted = $arr->sDate.'T'.$arr->sTime . ":00";
        $sdatetime = new DateTime($starttime_formatted);
        $endtime_formatted = $arr->eDate.'T'.$arr->eTime . ":00";
        $edatetime = new DateTime($endtime_formatted);
        if ($this->which_platform == 0) {
            // telegram
            $line = '<b>/// '.$sdatetime->format('D H:i').'-'.$edatetime->format('H:i').':</b> '.$arr->title. ' / '. $arr->text1. ' / ' .$arr->text2;
        } elseif ($this->which_platform == 1) {
            // discord
            $line = '**/// '.$sdatetime->format('D H:i').'-'.$edatetime->format('H:i').':** '.$arr->title. ' / '. $arr->text1. ' / ' .$arr->text2;;
        }
        
        return $line;
    }
}
?>