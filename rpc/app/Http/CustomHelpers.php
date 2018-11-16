<?php

/**
 * Generate the URL to a named route.
 *
 * @param  string  $name
 * @param  array   $parameters
 * @param  bool    $absolute
 * @return string
 */
function route($name, $parameters = [], $absolute = true)
{
    $appUrl = config('app.url');
    $appUrlSuffix = config('app.url_suffix');

    if ($appUrlSuffix && $absolute) {
        $relativePath = app('url')->route($name, $parameters, false);
        $url = $appUrl.$relativePath;
    } else {
        $url = app('url')->route($name, $parameters, $absolute);
    }
    
    return $url;
}