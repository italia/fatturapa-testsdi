<?php


namespace Lib;

use Models\Database;
use Models\Invoice;


class Exchange
{

    public static function Exchange()
    {
        new Database();
    }
    public static function receive($XML, $NomeFile)
    {
        Exchange::Exchange();
        $Invoice = Invoice::create(
            [
                'uuid' => '1c873278-dec8-'.rand(999,9999).'-8c69-7b647adca8ce',
                'nomefile' => $NomeFile,
                'posizione' => '',
                'cedente' => '',
                'anno' => '',
                'status' => 'E_RECEIVED',
                'blob' => $XML
            ]
        );        
        return $Invoice;
    }
    public static function checkValidity()
    {
    	Exchange::Exchange();
    	$Invoice = Invoice::all()->where('status', 'E_RECEIVED');		
		$Invoices=$Invoice->toArray();
					
		foreach($Invoices as $Invoice)
		{
			if(Exchange::validateInvoice($Invoice['blob'])=== true)
			{										
				Invoice::find($Invoice['uuid'])->update(['status' => 'E_VALID' ]);
						
			}
			else {				
				Invoice::find($Invoice['uuid'])->update(['status' => 'E_INVALID']);				
			}			
			
		}
		
		return true;
		
    	
   	}
    public static function deliver()
    {
    	
    }
    public static function checkExpiration()
    {
    	
    }
    public static function accept($invoices)
    {
    	
    }
	public static function refuse($invoices)
    {
    	
    }
	public static function validateInvoice($xml)
	{		
		return true;
	}
}
