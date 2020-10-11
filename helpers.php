<?php

if( ! function_exists('__'))
{
    function __($text, $textdomain = null, $context = '')
    {
        return ProcessWire\__($text, $textdomain, $context);
    }
}

if( ! function_exists('_n'))
{
    function _n($textSingular, $textPlural, $count, $textdomain = null)
    {
        return ProcessWire\_n($textSingular, $textPlural, $count, $textdomain);
    }
}


if (! function_exists('getTextdomain'))
{
    function getTextdomain(string $file)
    {
        $config = ProcessWire\wire('config');

        $textdomain = str_replace($config->paths->root, '', $file);
        $textdomain = str_replace('/', '--', $textdomain);
        $textdomain = str_replace('.', '-', $textdomain);

        return $textdomain;
    }
}
}