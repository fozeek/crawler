<?php

$timestart=microtime(true);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

require 'src/Page.php';
require 'src/Historical.php';
require 'src/HelperBis.php';
require 'src/Crawler.php';

$url = 'https://www.google.fr/search?hl=fr&q=petits+chatons';

$params = array();
if(PHP_SAPI == 'cli') {
    foreach ($argv as $key => $value) {
        if(isset($argv[$key-1]) && $argv[$key-1] == "-u") {
            $url = $argv[$key];
        } else if ($argv[$key] == "-l") {
            $params['log'] = true;
        } else if ($argv[$key] == "-r") {
            $params['render'] = true;
        } else if (isset($argv[$key-1]) && $argv[$key-1] == "-d") {
            $params['depth'] = $argv[$key];
        }
    }
    $params['eol'] = PHP_EOL;
    $params['tab'] = "\t";
} else {
    $params['render'] = true;
}

$options = array_merge(require 'configBis.php', $params);

$crawl = new Crawler($url, $options);

$timeend=microtime(true);
$time=$timeend-$timestart;
 
//Afficher le temps d'éxecution
$page_load_time = number_format($time, 3);
echo "Debut du script: ".date("H:i:s", $timestart);
echo $options['eol']."Fin du script: ".date("H:i:s", $timeend);
echo $options['eol']."Script execute en " . $page_load_time . " sec";