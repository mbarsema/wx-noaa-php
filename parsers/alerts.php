<?php

require_once 'libs/weather/parser.php';

class Weather_Parser_Alerts extends Weather_Parser {
	protected $m_kRec = array();
	protected $m_sData = '';

	public function startElement( $rParser, $sName, $kAttribs ){
		// Do nothing...
	}
	public function endElement( $rParser, $sName ){
		$sName = strtolower($sName);
		if( $sName == 'entry' ){
			$this->m_kData[] = $this->m_kRec;
			$this->m_kRec = array();
		}else{
			$this->m_kRec[$sName] = $this->m_sData;
		}
	}
	
	public function cdata( $rParser, $sData ){
		$this->m_sData = $sData;
	} 
	
	public function results(){
		$aResults = array();
		foreach( $this->m_kData as $nIndex => $kRec ){
			$aResults[] = array(
				'issued' => $kRec['published'],
				'last_updated' => $kRec['updated'],
				'title' => $kRec['title'],
				'summary' => $kRec['summary'],
				'type' => $kRec['cap:event'],
				'effective' => $kRec['cap:effective'],
				'expires' => $kRec['cap:expires'],
				'severity' => $kRec['cap:severity'],
				'areas' => $kRec['cap:areadesc'],
				'area_poly' => $kRec['cap:polygon'],
				'geocode' => $kRec['cap:geocode']
			);
		}
		return $aResults;
	}
}