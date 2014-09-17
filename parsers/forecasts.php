<?php

require_once 'libs/weather/parser.php';

class Weather_Parser_Forecast extends Weather_Parser {
	protected $m_kRec = array();
	protected $m_sData = '';
	protected $m_aValues = array();
	protected $m_aTemps = array();
	protected $m_aPrecip = array();
	protected $m_aLinks = array();
	protected $m_kCondition = array();
	protected $m_sType = null;
	protected $m_aConditions = array();

	public function startElement( $rParser, $sName, $kAttribs ){
		$sName = strtolower($sName);
		switch( $sName ){
			case 'temperature':
				$this->m_sType = ($kAttribs['TYPE'] == 'maximum' ) ? 'high_temp' : 'low_temp';
			break;
			case 'weather-conditions':
				$this->m_kCondition = array(
					'summary' => $kAttribs['WEATHER-SUMMARY']
				);
			break;
			case 'value':
				if(!empty($this->m_kCondition)){
					$this->m_kCondition['extended'][] = array(
						'coverage' => $kAttribs['COVERAGE'],
						'intensity' => $kAttribs['INTENSITY'],
						'additive' => $kAttribs['ADDITIVE'],
						'condition' => $kAttribs['WEATHER-TYPE'],
						'qualifier' => $kAttribs['QUALIFIER']
					);
				}
			break;
			default:
				// do nothing
			break;
		}
		
	}
	public function endElement( $rParser, $sName ){
		$sName = strtolower($sName);
		switch($sName){
			case 'value':
				$this->m_aValues[] = $this->m_sData;
				$this->m_sData = null;
			break;
			case 'temperature':
				$this->m_kTemps[$this->m_sType] = $this->m_aValues;
				$this->m_aValues = array();
			break;
			case 'probability-of-precipitation':
				$this->m_aPrecip = $this->m_aValues;
			break;
			case 'weather-conditions':
				$this->m_aConditions[] = $this->m_kCondition;
				$this->m_kCondition = array();
			break;
			case 'icon-link':
				$this->m_aLinks[] = $this->m_sData;
			break;
		}
	}
	
	public function cdata( $rParser, $sData ){
		$this->m_sData = $sData;
	}
	
	public function results(){
		$aResults = array();
		foreach( $this->m_kTemps as $sType => $aTemps ){
			foreach( $aTemps as $nIndex => $nTemp ){
				$aResults[$nIndex][$sType] = $nTemp;
			}
		}
		foreach( $this->m_aConditions as $nIndex => $kCondition ){
			$aResults[$nIndex]['conditions'] = $kCondition;
		}
		foreach( $this->m_aLinks as $nIndex => $sLink ){
			$aResults[$nIndex]['icon'] = $sLink;
		}
		foreach( $this->m_aPrecip as $nIndex => $nPrecip ){
			$aResults[($nIndex / 2)]['percent_chance'][] = $nPrecip;
		}
		
		return $aResults;
	}
}