<?php

class Help {
    public function __construct()
    {
        
    }

    public function handle($args) {
        $string = "<b>!help</b> Botti osaa toistaiseksi seuraavat komennot: \n
                    !np, !np chip, !np chill, !np stream2, !nytsoi, !kuuntelijat, !seuraava, !seuraavat. \n
                    /np, /np chip, /np chill, /np stream2, /nytsoi, /kuuntelijat, /seuraava, /seuraavat";
        return $string;
    }
}

?>