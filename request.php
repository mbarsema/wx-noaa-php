<?php

require_once 'libs/weather/config.php';
require_once 'libs/weather/parser.php';

class Weather_Request {
	
	private $m_kLocation = array();
	private $m_sType = '';
	private $m_sFormat = '';

	public function __construct( $aParams ){
		switch( $aParams[0] ){
			case 'city':
			case 'latlong':
			case 'zipcode':
				// do nothing
			break;
			default:
				throw new Exception( $aParams[0]. " is not valid" );
			break;
		}
	
	
		$this->m_kLocation = array(
			'type' => $aParams[0],
			'value' => $aParams[1]
		);
		list( $sFmt, $sQuery ) = explode('?', $aParams[2] );
		list( $this->m_sType, $this->m_sFormat ) = explode('.', $sFmt );
		
		switch( $this->m_sType ){
			case 'currents':
			case 'forecast':
			case 'alerts':
				// do nothing
			break;
			default:
				throw new Exception("Invalid type: " . $this->m_sType);
			break;
		}
	}

	public function request(){
		$kLoc = $this->resolveLocation( $kLocation );
		if(empty($kLoc)) return array();
		$kValue = $this->resolveWeather($kLoc);
		
		switch( $this->m_sFormat ){
			case 'txt': 
				foreach( $kValue as $sKey => $xValue ){
					echo $sKey . " " . $xValue . "<br/>";
				}
			break;
			case 'json':
				header('Content-Type: application/json');
				echo json_encode( $kValue );
			break;
			case 'html':
				if( $this->m_sType == 'currents' ){
					echo '<div id="currents" class="weather">'.
	 '<img id="weather-icon" style="float: left;" src="'.$kValue['icon_url_base'].$kValue['icon_url_name'].'">'.
	 '<span id="location">'.$kLoc['name'].'</span><br/>'.
	 '<span id="conditions">'.$kValue['weather'] . '</span><br clear="all"/>'.
	 '<span id="last_updated">'. $kValue['observation_time'].'</span><br/>'. 
	 '<span id="temp">Temperature: '. $kValue['temp_f'].'&deg;F</span><br/>'.
	 '<span id="humidity>Humidity: '. $kValue['relative_humidity'] . '</span><br/>'.
	 //'<span id="feels_like">Feels Like: '. $kValue['feels_like'] . '</span><br/>'.
	 '<span id="wind">Wind: '. $kValue['wind_dir'] . ' at ' . $kValue['wind_mph'] . 'mph</span><br/>'. 
	 '<span id="visibility">Visibility: '. $kValue['visibility_mi'] . 'mi</span><br/>'.
	 '</div>';
				}
			break;
			case 'xml':
				echo 'XML display here.';
			break;
		}
	}
	
	protected function resolveLocation(){
		
		
		if( $this->m_kLocation['value'] == 'Moline' || $this->m_kLocation['value'] == '61265' ){
			return array(
				'name' => 'Moline, IL',
				'metar' => 'KMLI',
				'lat' => 41.50,
				'long' => 90.52,
				'alerts' => 'ILC161'
			);
		}else if( $this->m_kLocation['value'] == 'Dubuque' || $this->m_kLocation['value'] == '52001' ){
			return array(
				'name' => 'Dubuque,IA',
				'metar' => 'KDBQ',
				'lat' => 42.50,
				'long' => 90.66,
				'alerts' => 'IAC061'
			);
		}else if( $this->m_kLocation['value'] == 'Omaha' || $this->m_kLocation['value'] == '68110' ){
			return array(
				'metar' => 'KOMA',
				'lat' => 41.30,
				'long' => 95.90,
				'alerts' => 'NEC055'
			);
		}else if( $this->m_kLocation['value'] == 'Chicago' || $this->m_kLocation['value'] == '60656' ){
			return array(
				'name' => 'Chicago, IL',
				'metar' => 'KORD',
				'lat' => 41.98,
				'long' => 87.90,
				'alerts' => 'ILC031'
			);
		}else if( $this->m_kLocation['value'] == 'New York City' || $this->m_kLocation['value'] == '11372' ){
			return array(
				'name' => 'New York City, NY',
				'metar' => 'KLGA',
				'lat' => 40.78,
				'long' => 73.87,
				'alerts' => 'NYC061'
			);
		}else if( $this->m_kLocation['value'] == 'San Francisco' || $this->m_kLocation['value'] == '94128' ){
			return array(
				'name' => 'San Francisco, CA',
				'metar' => 'KSFO',
				'lat' => 37.62,
				'long' => 122.38,
				'alerts' => 'CAC013'
			);
		}else if( $this->m_kLocation['value'] == 'Austin' || $this->m_kLocation['value'] == '78719' ){
			return array(
				'name' => 'Austin, TX',
				'metar' => 'KAUS',
				'lat' => 30.19,
				'long' => 97.67,
				'alerts' => 'TXC453'
			); 
		}
		return array(
			'metar' => '',
			'lat' => '',
			'long' => '',
			'alerts' => 'all'
		);
	}
	
	protected function resolveWeather( $kLocation ){
		$kConfig = Barse_Weather_Config::getConfig( );
		$sUrl = $kConfig['url'][$this->m_sType];
		switch( $this->m_sType ){
			case 'currents':
				$sUrl .= $kLocation['metar'].'.xml';
			break;
			case 'alerts':
				if( $kLocation['alerts'] != 'all' ){
					$sUrl .= '?x='.$kLocation['alerts'].'&y=1';
				}else{
					$sUrl = 'http://alerts.weather.gov/cap/us.php?x=1';
				}
			break;
			case 'forecast':
				$sUrl .= '?lat='.$kLocation['lat'].'&lon=-'.$kLocation['long'].
					 '&format=24+hourly&numDays=7';
			break;
			default:
				throw new Exception( $this->m_sType . " is not a valid type.");
			break;
		}
		
		$oParser = Barse_Weather_Parser::factory( $this->m_sType );
		$rCurl = curl_init();
		curl_setopt($rCurl, CURLOPT_URL, $sUrl);
		curl_setopt($rCurl, CURLOPT_HEADER, 0);
		curl_setopt($rCurl, CURLOPT_WRITEFUNCTION,array($oParser, 'parse'));
		curl_exec($rCurl);
		curl_close($rCurl);
		return $oParser->results();
	}
}