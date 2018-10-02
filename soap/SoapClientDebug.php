<?php

class SoapClientDebug extends SoapClient
{
    // https://www.binarytides.com/modify-soapclient-request-php/
    public function __doRequest($request, $location, $action, $version, $one_way = null)
    {
        echo('SoapClientDebug::__doRequest' . PHP_EOL);

        echo('request: '. $request . PHP_EOL);
        echo('location: '. $location . PHP_EOL);
        echo('action: '. $action . PHP_EOL);
        echo('version: '. $version . PHP_EOL);
        echo('one_way: '. $one_way . PHP_EOL);

        $soap_request = $request;
        
        $header = array(
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: "$action"',
            'Content-length: '.strlen($soap_request),
        );
        
        $soap_do = curl_init();
        
        $url = $location;
        
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            //CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            
            CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            CURLOPT_VERBOSE        => true,
            CURLOPT_URL            => $url ,
            
            CURLOPT_POSTFIELDS => $soap_request ,
            CURLOPT_HTTPHEADER => $header ,
        );
        
        curl_setopt_array($soap_do, $options);
        
        $output = curl_exec($soap_do);

        echo ('curl output = ');
        var_dump($output);
        $info = curl_getinfo($soap_do);
        echo ('curl info = ');
        var_dump($info);
        echo('curl http code = ' . $info['http_code']);

        if ($output === false) {
            $err = 'Curl error: ' . curl_error($soap_do);            
            print $err;
        } else {
            ///Operation completed successfully
        }
        curl_close($soap_do);
        
        // Uncomment the following line, if you actually want to do the request
        // return parent::__doRequest($request, $location, $action, $version);
        
        return $output;
    }
}
