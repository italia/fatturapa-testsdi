<?php


namespace Lib;

use Models\Database;
use Models\Invoice;


class Base
{

    public static function clear()
    {
    	return \Timecop::scale(1);       
    }
	public static function setTimeStamp($timestamp)
    {
       return \Timecop::travel(new \DateTime($timestamp));	  	
    }
	public static function setSpeed($speed)
    {
        return \Timecop::scale($speed); 
    }
	public static function getTimeStamp()
	{
		return (new \DateTime())->format("c");
	}
	public static function receive($invoice, $type, $notification_blob)
    {
       
    }
	public static function enqueue($invoice, $type, $notification_blob)
    {
       
    }
	public static function dispatch()
    {
       
    }
    
}
