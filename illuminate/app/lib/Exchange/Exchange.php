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
                'uuid' => '1c873278-dec8-4216-8c69-7b647adca8ce',
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
}
