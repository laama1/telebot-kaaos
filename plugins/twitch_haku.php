<?php
//include_once('/var/www/html/bot/twitch_helper_functions.php');
class Twitch_haku {

    /**
     * @date 2022-08-08
     * Currently a hack, if you want Telegram-style formatted text use 0, if Discord, use 1
     */
    private $which_platform = 0;
    private $curl = null;
    private $client_id = '';
    private $oauth_token = '';
    //private $th;
    private $debug = 1;

    public function __construct($which_platform = 0) {
        $this->which_platform = $which_platform;
        include __DIR__.'/../config.php';
        $this->client_id = $twitch_client_id;
        $this->oauth_token = $twitch_oauth_token;
        //$this->th = new Twitch_helper();
        
    }

    /**
     * arg[0] = command !twitch
     * arg[1] = twitch channel name
     * arg[2] = command: videos, follow
     * 
     */
    public function handle($args = null) : string {
        $data = '';
        //$user_data = $this->th->get_user_data($args[1]);
        $user_data = $this->get_user_data($args[1]);
        $this->logita('args:'.print_r($args, true));
        $this->logita('user_data:'.print_r($user_data, true));
        
        $description = $user_data->description;
        $profile_image = $user_data->profile_image_url;
        $offline_image = $user_data->offline_image_url;
        $user_id = $user_data->id;
        $display_name = $user_data->display_name;
        $login = $user_data->login;
        $views = $user_data->view_count;

        $user_url = 'https://twitch.tv/'.$login;

        switch ($args[2]) {

            case 'videot':
            case 'videos':
                $data = $this->get_user_videos($user_id);
                $this->logita('videos: '.$data);
                break;
            case 'info':
                $this->url = 'https://api.twitch.tv/helix/users/follows?to_id='.$user_id;
                $followers = $this->make_curl_twitch();
                //$this->logita(print_r($followers, true));
                $this->url = 'https://api.twitch.tv/helix/users/follows?from_id='.$user_id;
                $following = $this->make_curl_twitch();
                //$this->logita(print_r($following, true));

                //$data_d = "**{$display_name}**\n".
                $data_d = $description . "\n\n" .
                "**Views:** " . $views.
                ", **Followers:** ".$followers->total.
                ", **Following:** ".$following->total."\n";
                //$offline_image;
                $this->composeSimpleMsg($display_name, $profile_image, $offline_image, $user_url, $data_d);
                break;
            case 'follow':
                $data2 = $this->follow_user($user_id);
                $this->logita('data2:'.$data2);
                break;
            default:
                //$data = $description . "\n" . $profile_image;
                break;
        }
        return $data;
    }

    private function make_curl_twitch() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$this->oauth_token,
            'Client-id: '.$this->client_id,
            'Content-Type: application/json'));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result);
    }

    private function make_curl_discord(string $json_data) {
        $url = 'https://bot.8-b.fi/discord_gateway.php';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $this->logita(print_r($response, true));
        curl_close($ch);
	}

    private function composeSimpleMsg(string $username, string $avatar, string $image, string $url, string $message) {
        $timestamp = date("c", strtotime("now"));

        $json_data = json_encode([
            // Message
            //"content" => $message,
            
            // Username
            "username" => $username,

            // Avatar URL.
            "avatar_url" => $avatar,

            // Text-to-speech
            "tts" => false,

            // File upload
            // "file" => "",

            // Embeds Array
            "embeds" => [
                [
                    // Embed Title
                    //"title" => 'title',

                    // Embed Type
                    "type" => "rich",

                    // Embed Description
                    "description" => $message,

                    // URL of title link
                    "url" => $url,

                    // Timestamp of embed must be formatted as ISO8601
                    //"timestamp" => $timestamp,

                    // Embed left border color in HEX
                    "color" => hexdec("11aa22"),

                    // Footer
                    /*"footer" => [
                        //"text" => "testailua",
                        "icon_url" => "https://kaaosradio.fi/favicon.png"
                    ],*/

                    // Image to send
                    "image" => [
                        "url" => $image
                    ],

                    // Thumbnail
                    "thumbnail" => [
                        "url" => $avatar
                    ],

                    // Author
                    /*"author" => [
                        "name" => "laamanni",
                        "url" => "https://bot.8-b.fi"
                    ],*/

                    // Additional Fields array
                    "fields" => [
                        // Field 1
                        [
                            "name" => "Twitch Url",
                            "value" => $url,
                            "inline" => false
                        ],
                        // Field 2
                        /*[
                            "name" => "Field #2 Name",
                            "value" => "Field #2 Value",
                            "inline" => true
                        ]*/
                        // Etc..
                    ]
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        $this->logita("jes");
		$this->make_curl_discord($json_data);
        $this->logita("jes2");
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
        $result = $this->make_curl_twitch();
        return $result->data[0];
    }

    private function follow_user(string $user_id) {
        $this->logita('user_id: '. $user_id);
        return file_get_contents('https://bot.8-b.fi/twitch_subscribe_to_events.php?user_id='.$user_id.'&event_name=stream.online');
    }

    private function get_user_videos(string $user_id) :string {
        $returndata = '`';
        //$result = $this->th->get_videos($user_id);
        $this->url = 'https://api.twitch.tv/helix/videos?user_id=' . $user_id .'?limit=15';
        $result = $this->make_curl_twitch();
        $this->logita('result: '.print_r($result, true));
        $videos = array();
        foreach ($result->data as $video) {
            array_push($videos, $video);
        }
        #usort($videos, function ($a, $b) {
        #    return $a->published <=> $b->published;
        #});

        $index = 0;
        foreach ($videos as $avideo) {
            if ($index == 10) break;
            $returndata .= $avideo->title .' ('.$avideo->duration.') - '. $avideo->url . PHP_EOL;
            $index++;
        }
        $returndata .= '`';
        return $returndata;
    }

    public function logita(string $data) {
        if ($this->debug != 1) return;
        $dada = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        file_put_contents(__DIR__.'/../logs/twitch_haku_debug.log', date('Y-m-d H:i:s'). '; '.$dada[1]['class'].'; '.$dada[1]['function'].'; '.$dada[0]['line'].'; '.
                $data . PHP_EOL, FILE_APPEND);
    }
}