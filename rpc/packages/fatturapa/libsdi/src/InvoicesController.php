<?php

namespace Fatturapa\Libsdi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Invoice;
use Lib\Exchange;
use Lib\Issuer;
use Lib\Base;

class InvoicesController extends Controller
{
    
    public function index(Request $request)
    {
        $status = $request->input('status');
        $Invoices = Invoice::all()->where('status', $status)->where('actor', Base::getActor());
        
        return response()->json(array(
            'invoices' => $Invoices->toArray()));
    }
    public function checkValidity()
    {
        Exchange::checkValidity();
        echo "Check Validity";
        exit;
    }
    public function upload(Request $request)
    {
        $file = $request->file('File');
        $NomeFile = $file->getClientOriginalName();
        $XML = base64_encode(file_get_contents($file->getRealPath()));
        Issuer::upload($NomeFile, $XML);
        echo "Upload";
        exit;
    }
    public function transmit()
    {
        Issuer::transmit();
        echo "transmit";
        exit;
    }
    public function deliver()
    {
    }
    public function accept()
    {
    }
}
