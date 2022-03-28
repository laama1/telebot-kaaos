<?php

class Seuraavat {
    
    private $tulossa_api_address = 'https://kaaosradio.fi/tulevat/tulevat_api.php?komento=';

    public function __construct() {

    }
    public function handle($args) {
        $data = '';
        $query = isset($args[0]) ? ' '.$args[0] : '';
        if ($json = file_get_contents($this->tulossa_api_address.'seuraavat'.urlencode($query))) {
            if($newdata = json_decode($json)) {
                foreach ($newdata as $line) {
                    file_put_contents(__DIR__.'/../logs/seuraavat_logi.log', date('Y-m-d H:i:s').' --line: '.print_r($line, true), FILE_APPEND);
                    $data .= $this->format_line($line) . "\n";
                }
            }
        }
        return $data;
    }

    private function format_line($arr) {
        $starttime_formatted = $arr->sDate.'T'.$arr->sTime . ":00";
        $sdatetime = new DateTime($starttime_formatted);
        $endtime_formatted = $arr->eDate.'T'.$arr->eTime . ":00";
        $edatetime = new DateTime($endtime_formatted);

        $line = '<b>/// '.$sdatetime->format('D H:i').'-'.$edatetime->format('H:i').':</b> '.$arr->title. ' / '. $arr->text1. ' / ' .$arr->text2;
        return $line;
    }
}
/* For testing:*/
//$est = new Seuraavat();
//$data = $est->handle('2');
//echo "Received data: ".$data;
/**/
?>