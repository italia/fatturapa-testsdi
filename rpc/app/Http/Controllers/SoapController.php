<?php

namespace App\Http\Controllers\Soap;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Soap\Request\GetConversionAmount;
use App\Soap\Request\SdIRiceviFile;
use App\Soap\Response\GetConversionAmountResponse;
use App\Soap\Request\GetSdIRiceviFileResponse;

use App\Soap\Request\fileSdIBase_Type;
use App\Soap\Request\fileSdI_Type;
use App\Soap\Request\rispostaSdIRiceviFile_Type;

class SoapController extends Controller
{
	protected $prefix;
	
	protected $soapWrapper;
	
	public function __construct(SoapWrapper $soapWrapper){
		$this->prefix = DB::getTablePrefix();
		$this->soapWrapper = $soapWrapper;
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	
    	ini_set('soap.wsdl_cache_enabled',0);
		ini_set('soap.wsdl_cache_ttl',0);	
    	
		 $this->soapWrapper->add('SdIRi', function ($service) {
	      $service
	        ->wsdl('http://teamdigitale3.simevo.com/sdi/soap/SdIRiceviFile/SdIRiceviFile_v1.0.wsdl')
	        ->trace(true)
	        ->classmap([
	          fileSdIBase_Type::class,
	          fileSdI_Type::class,
	          rispostaSdIRiceviFile_Type::class,
	        ]);
	    });
				
	    
	    $NomeFile = 'cuccia.xml';
		$File = '23222';
	    
	    $response = $this->soapWrapper->call('SdIRi.RiceviFile', [
	      new fileSdIBase_Type($NomeFile, $File)
	     ]);
		 
		 
		
		//echo "<pre>";
		//print_r($response);
	    //var_dump($response);
	    echo "ff";
	    exit;
    	
		/*require __DIR__.'/../../../vendor/autoload.php';;
			
		$generator = new \Wsdl2PhpGenerator\Generator();
		echo '<pre>'; print_r($generator); exit;
		$generator->generate(
		    new \Wsdl2PhpGenerator\Config(array(
		        'inputFile' => './SdIRiceviFile/SdIRiceviFile_v1.0.wsdl',
		        'outputDir' => './SdIRiceviFile'
		    ))
		);*/
		
		/*require_once("vendor/wsdl2phpgenerator/wsdl2phpgenerator/src/config.php");
		//require_once("SdIRiceviFileHandler.php");
		$srv = new SoapServer('SdIRiceviFile_v1.0.wsdl');
		$srv->setClass("SdIRiceviFileHandler");
		$srv->handle();*/
		
		
        /*$datas = DB::select('select * from '.$this->prefix.'larvel_issuer where id=1');
		$datas2 = DB::select('select * from '.$this->prefix.'larvel_notifications where id=1');
		return view('test',array('datas'=>$datas,'datas2'=>$datas2));*/
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
