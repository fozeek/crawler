<?php

class Helper
{
    public static function buildUrl($url, $origin)
    {
        if(!$url) {
            return false;
        }
        $parsePageUrl = parse_url($origin);
        $parseUrl = parse_url($url);
        if(!isset($parseUrl['host'])) {
            if($url[0] != '/') {
                $url = '/' . $url;
            }
            $url = $parsePageUrl['host'] . $url;
        } 
        if(!isset($parseUrl['scheme'])) {
            if(substr($url, 0, 2) == '//') {
                $url = ':' . $url;
            }
            elseif(substr($url, 0, 3) != '://') {
                $url = '://' . $url;
            }
            $url = $parsePageUrl['scheme'] . $url;
        } 

        return $url;
    }

    public static function retrieveNode($node, $xml, $cb, $options = array())
    {
        if(!$xml) {
            return array();
        }
        $data = $xml->query("//" . $node); 
        $return = array();
        if($data) {
            foreach ($data as $key => $value) {
                $return[$key] = $cb($value);
            }
        }

        if(in_array('unique', $options)) {
            $return = array_unique($return);
        }
        if(in_array('filter', $options)) {
            $return = array_filter($return);
        }
        
        return $return;
    }

}