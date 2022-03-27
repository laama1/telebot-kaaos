<?php

class MyBot_Discord {
    //=======================================================================================================
    // Create new webhook in your Discord channel settings and copy&paste URL
    //=======================================================================================================

    // Discord #announcements
    private $_webhookurl = '';
    
    // Discord #botspam
    private $_botspam_webhook = '';

    private $_video_statsurl = '';
    private $_video_rtmpurl = '';
    private $_icecasturl = '';
    private $_kaaosradiourl = '';
    private $_weburl = '';

    public function __construct() {
        include dirname(__FILE__).'/config.php';
        $this->_video_statsurl = $rtmp_video_statsurl;
        $this->_video_rtmpurl = $rtmp_video_url;
        $this->_icecasturl = $icecast_url;
        $this->_kaaosradiourl = $kaaosradio_url;
        $this->_webhookurl = $discord_webhookurl;
        $this->_botspam_webhook = $discord_botspam_webhookurl;
        $this->_icecasturl = $icecast_url;
        $this->_weburl = $kaaosradio_video_page_url;

        if (isset($_GET) && isset($_GET['nytsoivideo'])) {
            $rtmpurl = '';
            $data = $_GET['nytsoivideo'];	// TODO: sanitize
            if (isset($_GET['rtmpurl'])) {
                // not in use currently
                $rtmpurl = $_GET['rtmpurl'];
            }
            $rtmpvideo_url = $this->parseRtmpLink();
            $this->composeMsg($data, $rtmpvideo_url);
        } elseif (isset($_GET) && isset($_GET['nytsoi'])) {
            // Kun pyyntö tulee esim. Irssi-skriptistä.
            $data = $_GET['nytsoi'];	// TODO: sanitize
            $this->composeFromIrssi($data, $this->_kaaosradiourl);
        } else if (isset($_REQUEST)) {
            // Kun pyyntö tulee laaman owncastista.
			$postdata = file_get_contents('php://input');
			$oc_data = json_decode($postdata);
			file_put_contents(dirname(__FILE__).'/logs/owncast.log', $oc_data->eventData->name, FILE_APPEND);
			if (isset($oc_data->eventData->streamTitle)) {
				$oc_data_send = $oc_data->eventData->streamTitle;
				$this->composeFromOwncast($oc_data_send);
			}
		}
    }

	private function composeFromOwncast($data) {
		$timestamp = date("c", strtotime("now"));
		$json_data = json_encode([
        	"content" => "Laamatestaa owncastia. ja Chattikin on!",
            "username" => "kaaosradio",
            "avatar_url" => "https://kaaosradio.fi/favicon.png",
            "tts" => false,
            "embeds" => [
				[
                    "title" => $data,
                    "type" => "rich",
                    "url" => "https://video.8-b.fi",
                    "timestamp" => $timestamp,
					"color" => hexdec("11aa22"),
                    "image" => [
                        "url" => "https://kaaosradio.fi/favicon.png"
                    ],
                    "fields" => [
                        [
                            "name" => "RTMP Url",
                            "value" => "rtmp://video.8-b.fi:1935",
                            "inline" => false
                        ],
                    ]
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		$this->makeCurl($json_data);
	}

	private function composeFromIrssi(string $data, string $url) {
		$timestamp = date("c", strtotime("now"));
		$json_data = json_encode([
        	"content" => "Kaaosradio Live audio!",
            "username" => "Icecast",
            "avatar_url" => "https://kaaosradio.fi/images/icons/cyan/play.png",
            "tts" => false,
            "embeds" => [
				[
                    "title" => $data,
                    "type" => "rich",
                    "url" => $url,
                    "timestamp" => $timestamp,
					"color" => hexdec("99cc22"),
                    /*"image" => [
                        "url" => "https://kaaosradio.fi/favicon.png"
                    ],*/
                    "fields" => [
                        [
                            "name" => "Icecast url:",
                            "value" => $this->_icecasturl,
                            "inline" => false
                        ],
                    ]
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		$this->makeCurl($json_data);
	}

    private function composeMsg($data, $url) {
        //=======================================================================================================
        // Compose message. You can use Markdown
        // Message Formatting -- https://discordapp.com/developers/docs/reference#message-formatting
        //========================================================================================================

        $timestamp = date("c", strtotime("now"));

        $json_data = json_encode([
            // Message
            "content" => "Kaaosradio Live! Check video and chat.",
            
            // Username
            "username" => "kaaosradio",

            // Avatar URL.
            "avatar_url" => "https://kaaosradio.fi/favicon.png",

            // Text-to-speech
            "tts" => false,

            // File upload
            // "file" => "",

            // Embeds Array
            "embeds" => [
                [
                    // Embed Title
                    "title" => $data,

                    // Embed Type
                    "type" => "rich",

                    // Embed Description
                    //"description" => "Description will be here, someday..",

                    // URL of title link
                    "url" => "https://videostream.kaaosradio.fi",

                    // Timestamp of embed must be formatted as ISO8601
                    "timestamp" => $timestamp,

                    // Embed left border color in HEX
                    "color" => hexdec("11aa22"),

                    // Footer
                    /*"footer" => [
                        //"text" => "testailua",
                        "icon_url" => "https://kaaosradio.fi/favicon.png"
                    ],*/

                    // Image to send
                    "image" => [
                        "url" => "https://kaaosradio.fi/favicon.png"
                    ],

                    // Thumbnail
                    //"thumbnail" => [
                    //    "url" => "https://kaaosradio.fi/favicon.png"
                    //],

                    // Author
                    /*"author" => [
                        "name" => "laamanni",
                        "url" => "https://kaaosradio.fi"
                    ],*/

                    // Additional Fields array
                    "fields" => [
                        // Field 1
                        [
                            "name" => "RTMP Url",
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

		$this->makeCurl($json_data);
    }

	private function makeCurl(string $json_data) {
	    $ch = curl_init($this->_webhookurl);
        //$ch = curl_init($this->_botspam_webhook);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);
	}

    private function parseRtmpLink() {
        $data = file_get_contents($this->_video_statsurl);
        $xml = simplexml_load_string($data);
        $new_rtmpurl = '';
        if (isset($xml->server)) {
            $applications = $xml->server->application;
            foreach ($applications as $app) {
                if (trim($app->name) != 'test') {       // FIXME: hardcoded value
                    if (isset($app->live->stream)) {
                        //$new_rtmpurl = $this->_video_rtmpurl.trim($app->live->stream->name);
                        $new_rtmpurl = $this->_video_rtmpurl.trim($app->name).'/'.trim($app->live->stream->name);
                        return $new_rtmpurl;
                    }
                }
            }
        }
        return $new_rtmpurl;
    }
}

$tsaet = new MyBot_Discord();
?>
