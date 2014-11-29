<?php

class Crawler
{

    private $url;
    public $options;
    private $page;
    public $historical;

    public function __construct($url, $options) {
        $this->url = $url;
        $this->options = $options;
        $this->historical = new Historical();
        $this->page = new Page($this, $this->url);

        if($this->options['render']) {
            echo $this->render();
        }
    }

    public function render() {
        $string = 'CRAWL : ' . $this->url . $this->options['eol'];
        $string .= 'Depth : ' . $this->options['depth'] . $this->options['eol'] . $this->options['eol'];

        return $string . $this->page->render();
    }

    public function __toString() {
        return $this->render();
    }

    public function isLog() {
        return $this->options['log'];
    }
}