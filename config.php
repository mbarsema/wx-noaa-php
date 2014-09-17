<?php

class Weather_Config
{
	public function getConfig(){
		return array(
			'url' => array(
				'alerts' => 'http://alerts.weather.gov/cap/wwaatmget.php',
				'currents' => 'http://w1.weather.gov/xml/current_obs/',
				'forecast' => 'http://graphical.weather.gov/xml/sample_products/browser_interface/ndfdBrowserClientByDay.php'
			)
		);
	}
}