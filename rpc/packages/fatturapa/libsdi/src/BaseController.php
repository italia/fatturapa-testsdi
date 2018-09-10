<?php

namespace Fatturapa\Libsdi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Invoice;
use Lib\Exchange;
use Lib\Base;


class BaseController extends Controller
{
	
    public function index(Request $request)
    {
    	
    				    			
    }
	public function clear()
	{
		Base::clear();		
		echo "clear";
		exit;
	}
	public function setdatetime(Request $request)
	{
		$timestamp = $request->input('timestamp');					
		$datetime = new \DateTime($timestamp);
		Base::setDateTime($datetime);
		echo "timestamp: ".$timestamp;
		exit;
	}	
	public function speed(Request $request)
	{
		$speed = $request->input('speed');
		Base::setSpeed($speed);
		echo "speed";
		exit;
	}
	public function getdatetime()
	{
		
		$dateTime=Base::getDateTime();
		
		echo "timestamp: " . $dateTime->date;
		exit;
	}

}
