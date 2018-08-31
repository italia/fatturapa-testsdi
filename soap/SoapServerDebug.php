<?php

// https://blog.mayflower.de/179-Extending-class-SoapServer-PHP5-for-debugging.html
class SoapServerDebug extends SoapServer
{
    /**
     * Array with all debug values
     *
     * @var array
     */
    protected $soapDebug = array();
    
    /**
     * Constructor
     *
     * @param mixed $wsdl
     * @param array[optional] $options
     */
    public function __construct($wsdl, $options = array())
    {
        return parent::__construct($wsdl, $options);
    }
    
    /**
     * Store a named value in the debug array.
     *
     * @param string $name
     * @param mixed $value
     */
    private function setDebugValue($name, $value)
    {
        $this->soapDebug[$name] = $value;
    }
    
    /**
     * Returns a value from the debug values.
     *
     * @param string $name
     * @return mixed
     */
    public function getDebugValue($name)
    {
        if (array_key_exists($name, $this->soapDebug)) {
            return $this->soapDebug[$name];
        }
        
        return false;
    }
    
    /**
     * Returns all debug values as array.
     *
     * @return array
     */
    public function getAllDebugValues()
    {
        return $this->soapDebug;
    }
    
    /**
     * Collect some debuging values and handle the soap request.
     *
     * @param string $request
     * @return void
     */
    public function handle($request = null)
    {
        // store the remote ip-address
        $this->setDebugValue('RemoteAddress', $_SERVER['REMOTE_ADDR']);
        
        // check variable HTTP_RAW_POST_DATA
        if (!isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents('php://input');
        }
        
        // check input param
        if (is_null($request)) {
            $request = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        
        // get soap namespace identifier
        if (preg_match(
            '/:Envelope[^>]*xmlns:([^=]*)="urn:NAMESPACEOFMYWEBSERVICE"/im',
            $request,
            $matches
        )) {
            $soapNameSpace = $matches[1];
            
            // grab called method from soap request
            $pattern = '/<' . $soapNameSpace . ':([^\/> ]*)/im';
            if (preg_match($pattern, $request, $matches)) {
                $this->setDebugValue('MethodName', $matches[1]);
            }
        }
        
        // store the request string
        $this->setDebugValue('RequestString', $request);
        
        // store the request headers
        if (function_exists('apache_request_headers')) {
            $this->setDebugValue('RequestHeader', serialize(apache_request_headers()));
        }
        
        // start output buffering
        ob_end_flush();
        ob_start();
        
        // finaly call SoapServer::handle() - store result
        $result = parent::handle($request);
        
        // store the response string
        $this->setDebugValue('ResponseString', ob_get_contents());
        
        // flush buffer
        ob_flush();
        
        // store the response headers
        if (function_exists('apache_response_headers')) {
            $this->setDebugValue('ResponseHeader', serialize(apache_response_headers()));
        }
        
        // return stored soap-call result
        return $result;
    }
}
