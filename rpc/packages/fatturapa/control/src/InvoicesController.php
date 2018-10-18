<?php

namespace FatturaPa\Control;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FatturaPa\Core\Models\Invoice;
use FatturaPa\Core\Actors\Exchange;
use FatturaPa\Core\Actors\Issuer;
use FatturaPa\Core\Actors\Base;
use FatturaPa\Core\Actors\Recipient;
use Illuminate\Support\Facades\Validator;

class InvoicesController extends Controller
{
    
    public function index(Request $request)
    {
        $fields = ['id', 'posizione', 'cedente', 'anno', 'status', 'actor', 'nomefile', 'ctime', 'issuer'];
        $status = $request->input('status');
        if ($status) {
            $invoices = Invoice::select($fields)
                ->where('status', $status)
                ->where('actor', Base::getActor());
        } else {
            $invoices = Invoice::select($fields)
            ->where('actor', Base::getActor());
        }
        return response()->json(array(
            'invoices' => $invoices->get()->toArray()));
    }
    public function checkValidity()
    {
        Exchange::checkValidity();
        echo "Check Validity";
        exit;
    }
    public function upload(Request $request)
    {
                
        $validator = Validator::make($request->all(), [
            'File' =>  'required|mimes:xml|max:5000',
        ]);
                
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $message) {
                echo $message;
            }
            abort(400);
            exit;
        }
                
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
    public function deliver(Request $request)
    {
        $dummy = $request->has('dummy');
        Exchange::deliver($dummy);
        echo "deliver $dummy";
        exit;
    }
    public function accept(Request $request, $id)
    {
        Recipient::accept($id);
        echo "accept";
        exit;
    }
    public function refuse(Request $request, $id)
    {
        Recipient::refuse($id);
        echo "refuse";
        exit;
    }
    public function checkExpiration(Request $request)
    {
        Exchange::checkExpiration();
        echo "Check Expiration";
        exit;
    }
}
