<?php

return array(
    'log' => false,
    'render' => false,
    'eol' => '<br />',
    'tab' => '&nbsp;&nbsp;&nbsp;&nbsp;',
    'depth' => 1,
    'domains' => array(
        'default' => array (
            'priority' => 0,
            'parsers' => array(
                'title' => function($content, $page) {
                    return Helper::retrieveNode('title', $content, function($node) {
                        return $node->nodeValue;
                    });
                },
                /*'h1' => function($content, $page) {
                    return Helper::retrieveNode('h1', $content, function($node) {
                        return $node->nodeValue;
                    });
                },*/
                'urls' => function($content, $page) {
                    return Helper::retrieveNode('a', $content, function($node) use ($page) {
                        return Helper::buildUrl($node->getAttribute('href'), $page->url);
                    }, ['unique', 'filter']);
                },
                'images' => function($content, $page) {
                    return Helper::retrieveNode('img', $content, function($node) use ($page) {
                        return array(
                            'src' => Helper::buildUrl($node->getAttribute('src'), $page->url),
                            'alt' => $node->getAttribute('alt')
                        );
                    });
                },
                'metas' => function($content, $page) {
                    return Helper::retrieveNode('meta', $content, function($node) {
                        return array(
                            'name' => $node->getAttribute('name'),
                            'property' => $node->getAttribute('property'),
                            'content' => $node->getAttribute('content'),
                            'http-equiv' => $node->getAttribute('http-equiv'),
                            'itemprop' => $node->getAttribute('itemprop'),
                        );
                    });
                }
            ),
        ),
        'google' => array (
            'priority' => 1,
            'checker' => function($url) {
                return (bool)preg_match('#google.(com|fr)#', $url);
            },
            'use_other_parsers' => array('images', 'metas', 'title', 'head', 'h1'),
            'parsers' => array(
                'urls' => function($content, $page) {
                    return Helper::retrieveNode('h3[@class="r"]/a', $content, function($node) use ($page) {
                        $url = $node->getAttribute('href');
                        if(strpos($url, '/images?') === false) {
                            $matche = array();
                            preg_match('/\/url\?q\=([^\&]*)/si', $url, $matche);
                            $url = urldecode($matche[1]);
                        }
                        return Helper::buildUrl($url, $page->url);
                    });
                }
            ),
        ),
        'facebook' => array (
            'priority' => 1,
            'checker' => function($url) {
                return (bool)preg_match('#facebook.(com|fr)#', $url);
            },
            'use_other_parsers' => false,
            'parsers' => array(
                'images' => function($content) {
                    return array();
                }
            ),
        ),
    ),
);