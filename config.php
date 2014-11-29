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
                    return Helper::retrieveNodeContent('title', $content);
                },
                'urls' => function($content, $page) {
                    return Helper::retrieveAttrInNode('a', 'href', $content, function($value) use ($page) {
                        return Helper::buildUrl($value, $page->url);
                    }, ['unique', 'filter']);
                },
                'images' => function($content, $page) {
                    return Helper::retrieveAttrInNode('img', ['src', 'alt'], $content, function($value, $context) use ($page) {
                        return $context == 'src' ? Helper::buildUrl($value, $page->url) : $value;
                    });
                },
                'metas' => function($content, $page) {
                    return Helper::retrieveAttrInNode('meta', ['name', 'property', 'content', 'http-equiv', 'itemprop'], $content);
                }
            ),
        ),
        'google' => array (
            'priority' => 1,
            'checker' => function($url) {
                return (bool)preg_match('#google.(com|fr)#', $url);
            },
            'use_other_parsers' => array('images', 'metas', 'title', 'head'),
            'parsers' => array(
                'urls' => function($content, $page) {
                    $matches = array();
                    preg_match_all('/<h3 class="r"><a href="\/url\?q\=([^\&]*)/si', $content, $matches);
                    return array_map(function($url) use ($page) {
                        return Helper::buildUrl($url, $page->url);
                    }, $matches[1]);
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