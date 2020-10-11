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

if (! function_exists('mix'))
{
    function mix($path, $manifestDirectory = '')
    {
        $config = ProcessWire\wire('config');

        $manifests = [];

        if (substr($path, 0, 1) !== '/') {
            $path = "/{$path}";
        }

        if ($manifestDirectory && substr($manifestDirectory, 0, 1) !== '/') {
            $manifestDirectory = "/{$manifestDirectory}";
        }

        $manifestPath = $config->paths->root . $manifestDirectory . 'mix-manifest.json';

        if (! isset($manifests[$manifestPath])) {
            if (! is_file($manifestPath)) {
                throw new Exception('The Mix manifest does not exist.');
            }

            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
        }

        $manifest = $manifests[$manifestPath];

        if (! isset($manifest[$path])) {
            $exception = new Exception("Unable to locate Mix file: {$path}.");

            if ($config->debug) {
                throw $exception;
            }

            return $path;
        }

        return $config->urls->httpRoot . $manifestDirectory.$manifest[$path];
    }
}