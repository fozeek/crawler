<?php

class Page
{

    public static $userAgent = 'Mozilla/5.0 (compatible; Thisisabot/2.1; +http://www.thisisabot.io/bot.html)';
    public static $followLocation = true;

    private $config;
    private $depth;

    private $parent;
    private $children = array();

    public $domains = array();
    public $url;
    public $response;
    public $info;
    public $retrieves = array();
    public $retrievesActions = array();

    public function __construct($crawler, $url, $depth = 0)
    {
        $this->crawler = $crawler;
        $this->config = $this->crawler->options;
        $this->url = $url;
        $this->depth = $depth;
        $this->checkDomain();

        $this->parse($this->crawl());
        if($this->depth < $this->config['depth']) {
            $this->crawlChildren();
        }
    }

    private function crawl()
    {
        $this->crawler->historical->add($this->url, $this);

        if($this->crawler->isLog()) {
            print "Crawl : " . $this->url . "\n";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, self::$followLocation);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, self::$userAgent);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );

        curl_setopt($ch, CURLOPT_URL, $this->url);

        $response = $this->response = curl_exec($ch);
        $this->info = curl_getinfo($ch);

        curl_close($ch);

        $doc = new DOMDocument();
        libxml_use_internal_errors(true); // for html5 tags
        $doc->loadHTML($response);
        libxml_use_internal_errors(false);
        $xpath = new DOMXpath($doc);
        return $xpath;
    }

    private function checkDomain()
    {
        foreach ($this->config['domains'] as $name => $domain) {
            if(isset($domain['checker'])) {
                $checker = $domain['checker'];
                if($checker($this->url)) {
                    $this->domains[$name] = $domain;
                }
            } else {
                $this->domains[$name] = $domain;
            }
        }

        uasort($this->domains, function($a, $b) {
            !isset($a['priority']) ? $a['priority'] = 0: null;
            !isset($b['priority']) ? $b['priority'] = 0: null;
            if ($a['priority'] == $b['priority']) {
                return 0;
            }
            return ($a['priority'] < $b['priority']) ? 1 : -1;
        });
    }

    private function parse($response)
    {
        $useOtherParsers = true;
        foreach ($this->domains as $domainName => $domain) {
            $go = false;
            if($useOtherParsers === true) {
                $go = true;
                $useOtherParsers = isset($domain['use_other_parsers']) ? $domain['use_other_parsers'] : true;
            }
            foreach ($domain['parsers'] as $name => $parser) {
                if(
                    $go 
                    || $useOtherParsers === true 
                    || (is_array($useOtherParsers) && in_array($name, $useOtherParsers))
                ) {
                    $this->retrievesActions[] = $domainName . '::' . $name;
                    $this->retrieves[$name] = $parser($response, $this);
                    if($this->crawler->isLog()) {
                        print "\t[" . $domainName . "::" . $name . "] : " . count($this->retrieves[$name]) . " found \n";
                    }
                }
            }
            if(isset($domain['use_other_parsers']) && !$domain['use_other_parsers']) {
                break;
            }
        }
    }

    private function crawlChildren()
    {
        foreach ($this->retrieves['urls'] as $url) {
            if($page = $this->crawler->historical->check($url)) {
                $this->children[] = $page;
            } else {
                $this->children[] = new Page($this->crawler, $url, $this->depth+1);
            }
        }
    }

    public function render($tabCount = 0)
    {
        $code = md5(urlencode($this->url));
        if($tabCount == 0) {
            file_put_contents('tmp/codes.php', '<?php return ' . var_export(array()) . ';');
        }
        file_put_contents('tmp/codes.php', '<?php return ' . var_export(array_merge(array($code =>$this->url), require 'tmp/codes.php')) . ';');
        var_dump(file_get_contents('tmp/codes.php'));
        echo var_export(array_merge(array($code =>$this->url), require 'tmp/codes.php'));
        die;
        file_put_contents('tmp/'. $code . '.html', $this->response);


        $eol = $this->config['eol'];
        $tab = $this->config['tab'];
        $tabs = '';
        for ($cpt = 0;$cpt < $tabCount;$cpt++) {
            $tabs .= $tab;
        }
        $domains = array();
        foreach ($this->domains as $name => $domain) {
            $domains[] = $name;
        }
        $string = $tabs .'--------------------------------------------------------------' . $eol
            . $tabs . 'URL : ' . $this->url . $eol
            . $tabs . 'Domains : ' . implode(' - ', $domains) . $eol
            . $tabs . 'Parsers : [' . implode('] [', $this->retrievesActions) . ']' . $eol
            . $tabs . 'Depth : ' . $this->depth . $eol
            . $tabs . 'Retrieves : ' . $eol;

        foreach ($this->retrieves as $name => $values) {
            $string .= $tabs . $tab . '[' . $name . '] => ' . $eol;
            foreach ($values as $value) {
                if(is_array($value)) {
                    $string .= $tabs . $tab . $tab . '[ ' . $eol;
                    foreach ($value as $key => $subvalue) {
                        $string .= $tabs . $tab . $tab . $tab . $key . ' => "' .$subvalue . '"' . $eol;
                    }
                    $string .= $tabs . $tab . $tab . '] ' . $eol;
                } else {
                    $string .= $tabs . $tab . $tab  . '"' . $value . '"' . $eol;
                }
            }
        }

        foreach ($this->children as $child) {
            $string .= $child->render($tabCount + 1);
        }
        return $string;
    }

}