<?php
namespace Telebot\Plugins;
/**
	UTF8 emojis:
	⛈️ Cloud With Lightning and Rain
	☁️ Cloud
	🌩️ Cloud With Lightning
	🌧️ Cloud With Rain
	🌨️ Cloud With Snow
	❄️ Snow flake
	🌪️ Tornado
	🌫️ Fog
	🌁 Foggy (city)
	⚡ High Voltage
	☔ Umbrella With Rain Drops
	🌂 closed umbrella
	🌈 rainbow
	🌥️ Sun Behind Large Cloud
	⛅ Sun Behind Cloud
	🌦️ Sun Behind Rain Cloud
	🌤️ Sun Behind Small Cloud
	🌄 sunrise over mountains
	🌅 sunrise
	🌇 sunset over buildings
	🌞 Sun With Face
	☀️ Sun
	🌆 cityscape at dusk
	🌉 bridge at night
	🌃 night with stars
	🌊 water wave
	🌀 cyclone
	🌬️ Wind Face
	💨 dashing away
	🍂 fallen leaf
	🌋 volcano
	🌏 earth globe asia australia
	🌟 glowing star
	🌠 shooting star
	🎆 fireworks
	🌌 milky way
	🌛 first quarter moon face
	🌝 full moon face
	🌜 last quarter moon face
	🌚 new moon face
	🌙 crescent moon
	🌑 new moon
	🌓 first quarter moon
	🌖 Waning gibbous moon
	🌒 waxing crescent moon
	🌔 waxing gibbous moon
	🦄 Unicorn Face
	🎠 carousel horse
	https://emojipedia.org/moon-viewing-ceremony/

**/

class Weather extends Template {
	private $apikey = '&units=metric&appid=';
	private $url = 'https://api.openweathermap.org/data/2.5/weather?';
	private $forecasturl = 'https://api.openweathermap.org/data/2.5/forecast?';
	private $areaUrl = 'https://api.openweathermap.org/data/2.5/find?cnt=5&lat=';
	private $uviurl = 'http://api.openweathermap.org/data/2.5/uvi?&lat=';
	private $uviforecastUrl = 'http://api.openweathermap.org/data/2.5/uvi/forecast?';
	private $db;
	private $dbpath;
	private $returnstr;
	#private $logenabled = 1;
	protected $logfile;

	public function __construct($msg = false) {

	}

	public function handle(array $args = []) : string {
		if ($args == null) return '';
		$this->logfile = __DIR__.'/../logs/weather_debug.txt';
		include __DIR__.'/../config.php';
		$this->dbpath = $weatherdb;
		$this->apikey .= $openwmapapikey;
		if ($string = $this->filter_command($args[0])) {
			return $string;
		}
		return '';
    }

	public function getMessage() {
		return $this->returnstr;
	}

	private function FINDUVINDEX($lat = false, $lon = false) {
		if ($lat == false || $lon == false) return false;
		$searchurl = $this->uviurl.$lat."&lon=$lon";
		$json = $this->request_api($searchurl);
		return $json->value;
	}

	private function request_api($url) {
		$url .= $this->apikey;
		$data = file_get_contents($url);
		return json_decode($data);
	}

	private function filter_command($msg) {
		$returnstring = '';
		if (preg_match('/!s (.*)$/', $msg, $city)) {
			$returnstring = $this->getSayLine($this->FINDWEATHER($city[1]));
		} elseif (preg_match('/\!(se ?)(.*)$/', $msg, $city)) {
			$returnstring = $this->FINDFORECAST($city[2]);
		} elseif (preg_match('/\!(sa ?)(.*)$/', $msg, $city)) {
			$returnstring = $this->FINDAREAWEATHER($city[2]);
		}
		return $returnstring;
	}

	private function getSayLine ($json = null) {
		if ($json == null) return false;
		$tempmin = round($json->main->temp_min,1);
		$tempmax = round($json->main->temp_max,1);
		$temp = '';
		if ($tempmin != $tempmax) {
			$temp = "({$tempmin}…{$tempmax})°C";
		} else {
			$temp = round($json->main->temp,1).'°C';
		}
		#$havaintotime = localtime($json->dt)->strftime('%H:%M');
		#$apptemp = $this->get_apperent_temp($json->{main}->{temp}, $json->{main}->{humidity}, $json->{wind}->{speed}, $json->{clouds}->{all}, $json->{coord}->{lat}, $json->{dt});
		$apptemp = round($json->main->feels_like);
		$sky = '';
		if ($apptemp) {
			#$apptemp = ', (~ '.$fi->format_number($apptemp, 1).'°C)';
			$apptemp = ' (~ '.$apptemp.'°C	)';
		} else {
			$apptemp = '';
		}
	
		$sunrise = '🌄 '.date('H:i', $json->sys->sunrise);
		$sunset = '🌆 ' .date('H:i',$json->sys->sunset);
		$wind_gust = '';
		if (isset($json->wind->gust)) {
			$wind_gust .= $json->wind->gust;
		}
		
		$wind_speed = round($json->wind->speed,1);
		$wind = '💨 '.$wind_speed;
		if($wind_gust != '') {
			$wind .= " ($wind_gust)";
		}
		$wind .= ' m/s';
		$city = $json->name;
		if ($city == 'Kokkola') {
			$city = '🦄 Kokkola';
		}
		$weatherdesc = '';
		$index = 1;
		foreach ($json->weather as $item ) {
			if ($index > 1) {
				$weatherdesc .= ', ';
			}
			$weatherdesc .= $item->description;
			$index++;
		}
		$uv_index = '';
		if (isset($json->uvindex) && $json->uvindex > 1) {
			$uv_index = ', UVI: '.$json->uvindex;
		}
		$newdesc = $this->replace_with_emoji($weatherdesc, $json->sys->sunrise, $json->sys->sunset, $json->dt);
		$returnvalue = '<b>'.$city.', '.$json->sys->country.':</b> '.$temp.$apptemp.', '.$newdesc.'. '.$sunrise.', '.$sunset.', '.$wind.$sky.$uv_index;
		$this->log(__LINE__.': hep, returnvalue: '.$returnvalue);
		return $returnvalue;
	}

	private function getSayLine2 ($json, $sunrise, $sunset) {
		$weatherdesc = '';
		$index = 1;
		foreach ($json->weather as $item) {
			if ($index > 1) {
				$weatherdesc .= ', ';
			}
			$weatherdesc .= $item->description;
			$index++;
		}
		#$returnvalue = $json->name.': '.$fi->format_number($json->main->temp, 1).'°C, '.replace_with_emoji($weatherdesc, $sunrise, $sunset, time);
		$returnvalue = $json->name.': '.round($json->main->temp,1).'°C, '.$this->replace_with_emoji($weatherdesc, $sunrise, $sunset, time());
		return $returnvalue;
	}

	private function FINDWEATHER ($searchword){
		$searchword = trim($searchword);
		$this->log(__LINE__.': hep searchword: '.$searchword);
		$newurl = '';
		$urltail = $searchword;
		if (preg_match('/(\d{5})/', $searchword, $results)) {
			$newurl = $this->url.'zip=';
			$urltail = $results[1].',fi';		# Search post numbers only from finland
		} else {
			$newurl = $this->url.'q=';
		}

		$this->log(__LINE__.": hep JSON1({$newurl}{$urltail}):");
		$json = $this->request_api($newurl.$urltail);
		//$this->log($json);

		$results = $this->GETCITYCOORDS($searchword);
		$this->log(__LINE__.': hep RESULTS:');
		$this->log(print_r($results, true));
		//$this->log(__LINE__.': hep');

		if ($results[2] && $json == '') {
			# city not found, use db value 
			$urltail = $results[2];
			$json = $this->request_api($newurl.$urltail);
			$this->log(__LINE__.': hep JSON2:');
			$this->log(print_r($json, true));
		}
		if (isset($results) && isset($results[0]) && isset($results[1])) {
			$json->uvindex = $this->FINDUVINDEX($results[0], $results[1]);
		}
		
		return $json;
	}
	
	private function FINDFORECAST ($searchword) {
		$returnstring = '';
		$json;
		$searchword = trim($searchword);
		
		if (preg_match('/(\d{5})/', $searchword, $results)) {
			$urltail = 'zip='.$results[1].',fi';		# Search post numbers only from finland
			$json = $this->request_api($this->forecasturl.$urltail);
		} else {
			$json = $this->request_api($this->forecasturl.'q='.$searchword);
		}
		if ($json == false) {
			if ($results = $this->GETCITYCOORDS($searchword)) {
				$json = $this->request_api($this->forecasturl.'q='.$results[2]);
			}
			if ($json == false) {
				return false;
			}
		}
		$index = 0;
		//$increment_hours = 0;
		foreach  ($json->list as $item) {
			if ($index >= 7) {
				# max 8 items: 8x 3h = 24h
				break;
			}
			if ($index == 0) {
				$returnstring = '<b>'.$json->city->name . ', '.$json->city->country.':</b> '.$returnstring;
			}
			$weathericon = $this->replace_with_emoji($item->weather[0]->main, $json->city->sunrise,	$json->city->sunset, $item->dt);
			$hour = date('H',$item->dt);
			$returnstring .= "<b>".sprintf('%.2d', $hour) .":</b> $weathericon ".round($item->main->temp, 1) .'°C, ';
			$index++;
		}
		return $returnstring;
	}

	private function FINDAREAWEATHER ($city) {
		$city = trim($city);
		$results = $this->GETCITYCOORDS($city);   # 1) find existing city from DB by search word
		$rubdata = $this->FINDWEATHER($city);                # 2) find one weather from API for sunrise & sunset times
		if (!isset($result[0]) && !isset($result[1]) && !isset($result[2]) && isset($rubdata->coord)) {
														# 3) if city was not found from DB
			$result[0] = $rubdata->coord->lat;
			$result[1] = $rubdata->coord->lon;
			$result[2] = $rubdata->name;
		}
		#($lat, $lon, $name) = GETCITYCOORDS($city) unless ($lat && $lon && $name);      # 3) find existing city again from DB
		#return 'City not found from DB or API.' unless ($lat && $lon && $name);
	
		$searchurl = $this->areaUrl.$result[0]."&lon=$result[1]";
		$json = $this->request_api($searchurl);
	
		$sayline = '';
		foreach  ($json->list as $city) {
			# TODO: get city coords from API and save to DB
			$sayline .= $this->getSayLine2($city, $rubdata->sys->sunrise, $rubdata->sys->sunset) . '. ';
		}
		return $sayline;
	}

	/**
	 * get city coordinates from DB
	 */
	private function GETCITYCOORDS ($city) {
		$city = "%{$city}%";
		$sql = 'SELECT DISTINCT LAT, LON,NAME from CITIES where NAME Like ? or (POSTNUMBER like ? AND POSTNUMBER is not null) LIMIT 1;';
		$this->log(__LINE__.': hep');
		if($results = $this->readDB($sql, array($city, $city))) {
			#var_dump($results);
			$this->log(__LINE__.': hep true');
			return array($results[0]['LAT'], $results[0]['LON'], $results[0]['NAME']);
		} else {
			$this->log(__LINE__.': hep false');
			return false;
		}
		
	}

	private function replace_with_emoji ($string, $sunrise, $sunset, $comparetime) {

		$sunmoon = $this->get_sun_moon($sunrise, $sunset, $comparetime);
		$string = preg_replace('/fog|mist/', '🌫️', $string);
		$string = preg_replace('/wind/', '💨', $string);
		$string = preg_replace('/snow/', '❄️', $string);
		$string = preg_replace('/clear sky/', $sunmoon, $string);
		$string = preg_replace('/Sky is Clear/', $sunmoon, $string);
		$string = preg_replace('/Clear/', $sunmoon, $string);			# short desc
		$string = preg_replace('/Clouds/', '☁️', $string);				# short desc
		$string = preg_replace('/Rain/', '🌧️', $string);				# short desc
		$string = preg_replace('/thunderstorm with rain/', '⛈️', $string);
		$string = preg_replace('/thunderstorm/', '⚡', $string);
		$string = preg_replace('/light rain/', '☔', $string);
		$string = preg_replace('/scattered clouds/', '☁', $string);
		
		$sunup = $this->is_sun_up($sunrise, $sunset, $comparetime);
		if ($sunup == 1) {
			$string = preg_replace('/overcast clouds/','🌥️',$string);
			$string = preg_replace('/broken clouds/','⛅',$string);
			$string = preg_replace('/few clouds/','🌤️',$string);
			$string = preg_replace('/light intensity shower rain/','🌦️',$string);
			$string = preg_replace('/shower rain/','🌧️', $string);
		} elseif ($sunup == 0) {
			$string = preg_replace('/shower rain/','🌧️',$string);
			$string = preg_replace('/broken clouds/','☁',$string);
			$string = preg_replace('/overcast clouds/','☁',$string);
		}
		return $string;
	}

	private function get_sun_moon ($sunrise, $sunset, $comparetime) {
		if ($this->is_sun_up($sunrise, $sunset, $comparetime) == 1) {
			return '🌞';
		}
		return $this->omaconway();
	}

	/**
	 * if $comparetime is between sunrise and sunset or not
	 */
	private function is_sun_up ($sunrise, $sunset, $comparetime) {
		$sunrise = $sunrise % 86400;
		$sunset = $sunset % 86400;
		$comparetime = $comparetime % 86400;
		if ($comparetime > $sunset || $comparetime < $sunrise) {
			return 0;
		}
		return 1;
	}

	private function omaconway () {
		# John Conway method
		#my ($y,$m,$d);
		$y = date('Y');
		$m = date('m');
		$d = date('d');
	
		$r = $y % 100;
		$r %= 19;
		if ($r > 9) { $r-= 19; }
		$r = (($r * 11) % 30) + $m + $d;
		if ($m < 3) { $r += 2; }
		$r -= 8.3;              # year > 2000
	
		$r = ($r + 0.5) % 30;	#test321
		$age = $r;
		$r = 7/30 * $r + 1;
	
		/*
		  0: 'New Moon'        🌑
		  1: 'Waxing Crescent' 🌒
		  2: 'First Quarter',  🌓
		  3: 'Waxing Gibbous', 🌔
		  4: 'Full Moon',      🌕
		  5: 'Waning Gibbous', 🌖
		  6: 'Last Quarter',   🌗
		  7: 'Waning Crescent' 🌘
		*/
	
		$moonarray = array('🌑', '🌒', '🌓', '🌔', '🌕', '🌖', '🌗', '🌘');
		return $moonarray[$r];
	}

	/**
	 * TODO: return false on fail.
	 */
	private function createDBConnection() {
		#$this->log(__LINE__.': hepDO');
		$this->db = new \PDO("sqlite:$this->dbpath");
		#$this->log(__LINE__.': hep PDO');
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		return true;
	}

	private function readDB($sql = false, $params = false) {
		if ($sql == false) return false;
		$this->createDBConnection();
		try {
			if ($pdostmt = $this->db->prepare($sql)) {
				if ($pdostmt->execute($params)) {
					return $pdostmt->fetchAll();
				}
			}
		} catch(\PDOException $pe) {
			$this->log(__LINE__.': PDO Exception: '. $pe->getMessage());
		} catch(\Exception $e) {
			$this->log(__LINE__.': Exception: '.$e->getMessage());
		}
		return false;
	}

	# insert line into Database
	private function insertIntoDB($sqlString = null, $params = null) {
		if ($sqlString === null) return false;
		#$this->pi(__FUNCTION__.": sqlString: " .$sqlString);
		try {
			if ($pdostmt = $this->db->prepare($sqlString)) {
				if ($pdostmt->execute($params)) {
					#$this->db = null;
					//return $pdostmt->lastInsertRowID();
					return true;
				} else {
					#$this->pe(__FUNCTION__.": ERROR.. $sqlString");
					#$this->pa($pdostmt);
				}
			} else {
				#$this->pe(__FUNCTION__.": prepare statement error.");//: ".$pdostmt->errorInfo);
			}
		} catch(\PDOException $e) {
			#$this->pe(__FUNCTION__.": PDOException: ".print_r($e,1));
		} catch(\EXCeption $e) {
			#$this->pe(__FUNCTION__.": Exception: ".$e);
		}
		#$this->db = null;
		return false;
	}

    protected function log($text) {
        file_put_contents($this->logfile, date('Y-m-d H:i:s').': '.$text . PHP_EOL, FILE_APPEND);
    }
	
}

//$w = new Weather();
//$w->handle(['Jyväskylä']);
