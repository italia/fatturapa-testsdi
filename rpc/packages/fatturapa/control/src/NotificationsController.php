<?php

namespace FatturaPa\Control;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FatturaPa\Core\Models\Notification;
use FatturaPa\Core\Actors\Base;
use FatturaPa\Core\Actors\Recipient;
use FatturaPa\Core\Actors\Exchange;

class NotificationsController extends Controller
{
    
    public function index(Request $request)
    {
        $fields = ['id', 'invoice_id', 'type', 'status', 'actor', 'nomefile', 'ctime'];
        $status = $request->input('status');
        if ($status) {
            $notifications = Notification::select($fields)
                ->where('actor', Base::getActor())
                ->where('status', $status);
        } else {
            $notifications = Notification::select($fields)
                ->where('actor', Base::getActor());
        }
        return response()->json(array(
            'notifications' => $notifications->get()->toArray()));
    }
    public function dispatchi()
    {
        $actor=Base::getActor();
        if ($actor=='sdi') {
            Exchange::dispatchi();
        } else {
            Recipient::dispatchi();
        }
        
        echo "dispatch";
        exit;
    }
    public function notification(Request $request, $id)
    {
        $notification = Notification::all()->where('id', $id);
        return response()->json($notification[0]);
    }
    public function deliver()
    {
        // Base::
    }
}
