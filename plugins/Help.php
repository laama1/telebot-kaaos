<?php
namespace Telebot\Plugins;
class Help extends Template {

    public function handle(array $args = []): string {
        $string = "<b>!help</b> Botti osaa toistaiseksi seuraavat komennot: \n
                    !np, !np chip, !np chill, !np stream2, !nytsoi, !kuuntelijat, !seuraava, !seuraavat, !sober. \n
                    /np, /np chip, /np chill, /np stream2, /nytsoi, /kuuntelijat, /seuraava, /seuraavat";
        return $string;
    }
}

?>