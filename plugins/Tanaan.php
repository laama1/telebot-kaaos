<?php
namespace Telebot\Plugins;
class Tanaan extends Template {
    
    private $tulossa_api_address = 'https://kaaosradio.fi/tulevat/tulevat_api.php?komento=';
    private $onlyone = 0;
    protected $which_platform = 0;

    public function __construct($onlyone = 0, $which_platform = 0) {
        $this->onlyone = $onlyone;
        $this->which_platform = $which_platform;
    }

    public function howMany(int $onlyone = 0): void {
        $this->onlyone = $onlyone;
    }

    public function handle(array $args = []): string {
        $data = '';
        $query = isset($args[1]) ? ' '.$args[1] : '';
        $request_address = $this->tulossa_api_address.'today'.urlencode($query);
        if ($json = file_get_contents($request_address)) {
            if($newdata = json_decode($json)) {
                foreach ($newdata as $line) {
                    $data .= $this->format_line($line) . "\n";
                    if($this->onlyone == 1) break;
                }
            }
        }
        return $data;
    }

    private function format_line($arr): string {
        $starttime_formatted = $arr->sDate.'T'.$arr->sTime . ":00";
        $sdatetime = new \DateTime($starttime_formatted);
        $endtime_formatted = $arr->eDate.'T'.$arr->eTime . ":00";
        $edatetime = new \DateTime($endtime_formatted);
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
