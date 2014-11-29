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

    public static function retrieveAttr($attr, $glob)
    {
        if(!$glob) {
            return false;
        }
        $matches = array();
        preg_match_all('/' . $attr . ' ?= ?[\"\']([^(\"|\')]*)[\"\']/', $glob, $matches);
        if(isset($matches[1][0])) {
            return $matches[1][0];
        } else {
            return false;
        }
    }

    public static function retrieveNode($node, $glob)
    {
        if(!$glob) {
            return array();
        }
        $matches = array();
        preg_match_all("/\<" . $node . "([^\>]*)\>/", $glob, $matches);
        if(isset($matches[1])) {
            return $matches[1];
        } else {
            return array();
        }
    }

    public static function retrieveNodeContent($node, $glob, $cb = false)
    {
        if(!$glob) {
            return array();
        }
        $matches = array();
        preg_match_all("/\< ?" . $node . "[^\>]* ?\>(.*)<\/ ?" . $node . " ?\>/U", $glob, $matches);
        if(isset($matches[1])) {
            if($cb) {
                return array_map(function($content) use ($cb) {
                    return $cb($content);
                }, $matches[1]);
            }
            return $matches[1];
        } else {
            return array();
        }
    }

    public static function retrieveAttrInNode($node, $attr, $glob, $cb = false, $options = array())
    {
        $data = array_map(function($node) use ($attr, $cb) {
            if(is_array($attr)) {
                $data = array();
                foreach ($attr as $name) {
                    $data[$name] = $cb ? $cb(self::retrieveAttr($name, $node), $name) : self::retrieveAttr($name, $node, $name);
                }
            } else {
                $data = $cb ? $cb(self::retrieveAttr($attr, $node)) : self::retrieveAttr($attr, $node);
            }
            return $data;
        }, self::retrieveNode($node, $glob));
        if(in_array('unique', $options)) {
            $data = array_unique($data);
        }
        if(in_array('filter', $options)) {
            $data = array_filter($data);
        }
        return $data;
    }
}