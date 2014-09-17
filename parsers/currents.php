<?php

require_once 'libs/weather/parser.php';

class Weather_Parser_Currents extends Weather_Parser {
	protected $m_kRec = array();
	protected $m_sData = '';

	public function startElement( $rParser, $sName, $kAttribs ){
		$sName = strtolower($sName);
		$this->m_kRec[$sName] = $kAttribs;
	}
	public function endElement( $rParser, $sName ){
		$sName = strtolower($sName);
		$this->m_kData[$sName] = $this->m_sData;
		$this->m_kRec[$sName] = array();
	}
	
	public function cdata( $rParser, $sData ){
		$this->m_sData = $sData;
	} 
	
	public function results( ){
		return array(
			'location' => array(
				'metar' => $this->m_kData['station_id'],
				'lat' => $this->m_kData['latitude'],
				'long' => $this->m_kData['longitude'],
				'last_updated' => $this->m_kData['observation_time_rfc822']
			),
			'conditions' => $this->m_kData['weather'],
			'temp' => $this->m_kData['temp_f'],
			'relative_humidity' => $this->m_kData['relative_humidity'],
			'wind' => array(
				'direction' => $this->m_kData['wind_dir'],		
				'degrees' => $this->m_kData['wind_degrees'],
				'mph' => $this->m_kData['wind_mph'],
			),
			'barometer' => $this->m_kData['pressure_in'],
			'dewpoint' => $this->m_kData['dewpoint_f'],
			'windchill' => $this->m_kData['windchill_f'],
			'visibility' => $this->m_kData['visibility_mi'],
			'default_icon' => $this->m_kData['icon_url_base'] . $this->m_kData['icon_url_name']
		);
	}
}