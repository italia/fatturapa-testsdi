<?php

namespace App\Http\Controllers;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Soap\Request\GetConversionAmount;
//use App\Soap\Response\GetConversionAmountResponse;

class TestController extends Controller
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
    	
		 $this->soapWrapper->add('Currency', function ($service) {
	      $service
	        ->wsdl('http://currencyconverter.kowabunga.net/converter.asmx?WSDL')
	        ->trace(true)
	        ->classmap([
	          GetConversionAmount::class
	          //GetConversionAmountResponse::class,
	        ]);
	    });
		
		var_dump($this->soapWrapper);
		exit;
	
	    // Without classmap
	    $response = $this->soapWrapper->call('Currency.GetConversionAmount', [
	      'CurrencyFrom' => 'USD','CurrencyTo'   => 'EUR','RateDate'     => '2014-06-05','Amount'       => '1000',
	    ]);
	
	    //var_dump($response);
		echo "<pre>";
		print_r($response);
	    // With classmap
	    $response = $this->soapWrapper->call('Currency.GetConversionAmount', [
	      new GetConversionAmount('USD', 'INR',date('Y-m-d'), '1')
	    ]);
		
		echo "<pre>";
		print_r($response);
	    //var_dump($response);
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
