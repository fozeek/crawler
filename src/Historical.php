<?php

class Historical
{

    private $historical = array();

    public function add($url, $page)
    {
        $this->historical[$url] = $page;
    }

    public function check($url)
    {
        if(isset($this->historical[$url])) {
            return $this->historical[$url];
        }
        return false;
    }

}