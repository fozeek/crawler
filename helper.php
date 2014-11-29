<?php

class Helper
{
	public static function buildUrl($url, $reference)
	{
		$parsePageUrl = parse_url($reference);
		$parseUrl = parse_url($url);
		if(!isset($parseUrl['scheme']) || !isset($parseUrl['host'])) {
			return $parsePageUrl['scheme'] . '://' . $parsePageUrl['host'] . $url;
		} else {
			return $url;
		}
	}

}