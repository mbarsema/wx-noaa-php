<?php

abstract class Weather_Parser{
	protected $m_rParser = null;
	protected $m_kData = array();
	
	public function factory( $sType ){
		require_once 'libs/weather/parser/'.$sType.'.php';
		$sClass = 'Barse_Weather_Parser_'.ucfirst( $sType );
		return new $sClass( );
	}
	
	function __construct( ){
		$this->m_rParser = xml_parser_create();
		xml_set_element_handler($this->m_rParser, array($this, 'startElement'), array($this, 'endElement'));
		xml_set_character_data_handler($this->m_rParser, array($this, 'cdata'));
	}
	
	function __destruct( ){
		xml_parser_free( $this->m_rParser );
	}
	
	public function parse( $rCurl, $sBuffer ){
		xml_parse($this->m_rParser, $sBuffer );
		return strlen( $sBuffer );
	}
	
	public function results( ){
		return $this->m_kData;
	}
	
	abstract public function startElement( $rParser, $sName, $kAttribs );
	abstract public function endElement( $rParser, $sName );
	abstract public function cdata( $rParser, $sData );
	
}