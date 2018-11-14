<?php 

/**
 * Wrapper di SoapClient con:
 * - supporto a MTOM
 * - supporto ai certificati
 */
class SdiSoapClient extends \SoapClient
{
    const USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';
    const REGEX_ENV  = '/<soap[\s\S]*nvelope>/i';
    const REGEX_XOP  = '/<xop:include[\s\S]*cid:%s@[\s\S]*?<\/xop:Include>/i';
    const REGEX_CID  = '/cid:([0-9a-zA-Z-]+)@/i';
    const REGEX_CON  = '/Content-ID:[\s\S].+?%s[\s\S].+?>([\s\S]*?)--MIMEBoundary/i';
    
    /**
     * Path al certificato della CA
     * @var string
     */
    public $caCertFile;
    
    /**
     * Path alla chiave privata
     * @var string
     */
    public $privateKeyFile;
    
    /**
     * Path al certificato client
     * @var string
     */
    public $clientCertFile;
    
    /**
     * Url del proxy (es. host:port)
     * @var string
     */
    public $proxyUrl;
    
    /**
     * Autenticazione del proxy (es. username:password)
     * @var string
     */
    public $proxyAuth;
    
    /**
     * Headers dell'ultima richiesta
     * @var array
     */
    private $lastRequestHeaders;
    
    /**
     * Headers dell'ultima risposta
     * @var array
     */
    private $lastResponseHeaders;
    
    /**
     * Body dell'ultima richiesta
     * @var string
     */
    private $lastRequestBody;
    
    /**
     * Body dell'ultima risposta
     * @var string
     */
    private $lastResponseBody;
    
    
    /**
     * @inheritdoc
     */
    public function __doRequest($request, $location, $action, $version, $one_way = null)
    {
        // reset
        $this->lastResponseBody = '';
        $this->lastResponseHeaders = array();

        $this->lastRequestHeaders = array(
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: '.$action,
            'Content-length: ' . strlen($request),
        );
        $this->lastRequestBody = $request;
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_ENABLE_ALPN, false);
        curl_setopt($ch, CURLOPT_SSLKEY, $this->privateKeyFile);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->clientCertFile);
        curl_setopt($ch, CURLOPT_CAINFO, $this->caCertFile);

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_URL, $location);
        curl_setopt($ch, CURLOPT_POST , true);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->lastRequestHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->lastRequestBody);
        
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this, 'handleHeaderLine'));
        
        if( !empty($this->proxyUrl) ){
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyUrl);
            if( !empty($this->proxyAuth) ){
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyAuth);
            }
        }
        
        $this->lastResponseBody = curl_exec($ch);
        
        if ( false === $this->lastResponseBody ) {
            $err_num  = curl_errno($ch);
            $err_desc = curl_error($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            
            throw new \Exception('[HTTP:'. $httpcode .'] ' . $err_desc, $err_num);
        }
        
        curl_close($ch);
        
        $this->lastResponseBody = $this->__processResponse($this->lastResponseBody);
        
        return $this->lastResponseBody;
    }
    
    /**
     * Processa la risposta per supportare il formato MTOM
     * NB teniamo il metodo pubblico per favorire i test unitari
     * @param string $response
     * @throws \Exception
     * @return string
     */
    public function __processResponse($response){
        
        $xml_response = null;
        
        // recupera la risposta xml isolandola da quella mtom
        preg_match(self::REGEX_ENV, $response, $xml_response);
        
        if ( !is_array($xml_response) || count($xml_response) <= 0 ) {
            throw new \Exception('No XML has been found.');
        }
        // prendiamo il primo elemento dell'array
        $xml_response = reset($xml_response);

        // recuperiamo i tag xop
        $xop_elements = null;
        preg_match_all(sprintf(self::REGEX_XOP, '.*'), $response, $xop_elements);
        // prendiamo il primo elemento dell'array
        $xop_elements = reset($xop_elements);
        
        if ( is_array($xop_elements) && count($xop_elements) > 0 ) {
            foreach ($xop_elements as $xop_element) {
                
                // recuperiamo il cid
                $matches = null;
                preg_match(self::REGEX_CID, $xop_element, $matches);
                
                if( isset($matches[1]) ){
                    $cid = $matches[1];
                    
                    // recuperiamo il contenuto associato al cid
                    $matches = null;
                    preg_match(sprintf(self::REGEX_CON, $cid), $response, $matches);
                    
                    if( isset($matches[1]) ){
                        $binary = trim($matches[1]);
                        $binary = base64_encode($binary);
                        
                        // sostituiamo il tag xop:Include con base64_encode(binary)
                        // nota: SoapClient fa automaticamente il base64_decode(binary)
                        $old_xml_response = $xml_response;
                        $xml_response = preg_replace(sprintf(self::REGEX_XOP, $cid), $binary, $xml_response);
                        if( $old_xml_response === $xml_response ){
                            throw new \Exception('xop replace failed');
                        }
                    } else {
                        throw new \Exception('binary not found.');
                    }
                } else {
                    throw new \Exception('cid not found.');
                }
            }
        }
        
        return $xml_response;
    }
    
    /**
     * @inheritdoc
     */
    public function __getLastRequestHeaders(){
        return implode("\n", $this->lastRequestHeaders);
    }
    
    /**
     * @inheritdoc
     */
    public function __getLastResponseHeaders(){
        return implode("\n", $this->lastResponseHeaders);
    }
    
    /**
     * @inheritdoc
     */
    public function __getLastRequest(){
        return $this->lastRequestBody;
    }
    
    /**
     * @inheritdoc
     */
    public function __getLastResponse(){
        return $this->lastResponseBody;
    }
    
    /**
     * Handle singolo header richiesta cURL
     * return integer
     */
    public function handleHeaderLine($curl, $header_line){
        $this->lastResponseHeaders[] = $header_line;
        return strlen($header_line);
    }
}
