<?php

class Twitch_haku {

    /**
     * @date 2022-08-08
     * Currently a hack, if you want Telegram-style formatted text use 0, if Discord, use 1
     */
    private $which_platform = 0;
    private $curl = null;
    private $client_id = '';
    private $oauth_token = '';

    public function __construct($which_platform = 0) {
        $this->which_platform = $which_platform;
        include __DIR__.'/../config.php';
        $this->client_id = $twitch_client_id;
        $this->oauth_token = $twitch_oauth_token;
        
    }

    /**
     * arg[0] = command !twitch
     * arg[1] = twitch channel name
     * arg[2] = command: videos (tulossa: clips, mitä muuta?)
     * 
     */
    public function handle($args = null) : string {
        $data = '';
        $user_data = $this->get_user_data($args[1]);
        
        $description = $user_data->description;
        $profile_image = $user_data->profile_image_url;
        $user_id = $user_data->id;
        $this->logita("user_id: ".$user_id);
        
        switch ($args[2]) {

            case 'videot':
            case 'videos':
                //$this->logita("Get user videos next...");
                $data = $this->get_user_videos($user_id);
                break;
            case 'info':
                $data = $description . "\n" . $profile_image;
                break;
            default:
                $data = $description . "\n" . $profile_image;
                break;
        }
        return $data;
        //$this->logita("DATA: ".$data);
        //return '';
    }

    private function make_curl() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$this->oauth_token,
            'Client-id: '.$this->client_id,
            'Content-Type: application/json'));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        $result = curl_exec($curl);
        curl_close($curl);
        //$this->logita("Curl result: ".$result);
        return json_decode($result);
    }

    private function get_user_data(string $nick) {
        /**
         * {
         *   "data": [
         *      {
         *      "id": "485013369",
         *      "login": "kaaosradio",
         *      "type": "",
         *      "broadcaster_type": "",
         *      "description": "Underground netradio from Finland. LIVE audio / video streams, mixtapes, radio shows, 24h playlists. techno, trance, dance, dnb, dubstep, breakbeat, electro, chiptune, hard -trance/techno/house/style/core, psytrance, freeform, IDM, chillout..",
         *      "profile_image_url": "https://static-cdn.jtvnw.net/jtv_user_pictures/eb29221e-e40d-4479-b563-5fef41493876-profile_image-300x300.png",
         *      "offline_image_url": "https://static-cdn.jtvnw.net/jtv_user_pictures/84c421a5-8ccb-4ea1-a56f-f7674a2073b6-channel_offline_image-1920x1080.png",
         *      "view_count": 3153,
         *      "created_at": "2020-01-11T13:20:10Z"
         *      }
         *  ]
         * }
         */
        $this->url = 'https://api.twitch.tv/helix/users?login=' .$nick;
        $result = $this->make_curl();
        //var_dump($result);
        //$this->logita("user data:" .print_r($result->data[0], true));
        return $result->data[0];
    }

    private function get_user_videos(string $broadcaster_id) :string {
        $returndata = '`';
        $this->url = 'https://api.twitch.tv/helix/videos?user_id=' . $broadcaster_id;
        $result = $this->make_curl();
        $videos = array();
        foreach ($result->data as $video) {
            //$this->logita("Bling");
            array_push($videos, $video);
        }
        $this->logita("Videos (".count($videos).'): ' .print_r($videos, true));
        #return $returndata;

        #usort($videos, function ($a, $b) {
        #    return $a->published <=> $b->published;
        #});

        $index = 0;
        foreach ($videos as $avideo) {
            if ($index == 10) break;
            $returndata .= $avideo->title .' ('.$avideo->duration.') - '. $avideo->url . "\n";
            $index++;
        }
        $returndata .= '`';
        return $returndata;
    }


    private function logita(string $dada) {
        file_put_contents(__DIR__.'/../logs/twitch_debug.txt', $dada . PHP_EOL, FILE_APPEND);
    }
}


?>