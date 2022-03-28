<?php

class Help {
    public function __construct()
    {
        
    }

    public function handle($args) {
        $string = "<b>!help</b> Botti osaa toistaiseksi seuraavat komennot: !np, !np chip, !np chill, !np stream2, !nytsoi, !kuuntelijat, !seuraavat.";
        return $string;

    }
}

?>