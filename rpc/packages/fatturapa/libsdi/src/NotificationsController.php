<?php

namespace Fatturapa\Libsdi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Notification;
use Lib\Base;

class NotificationsController extends Controller
{
    
    public function index(Request $request)
    {
        $notifications = Notification::all()->where('actor', Base::getActor());
        
        return response()->json(array(
            'notifications' => $notifications->toArray()));
    }
    public function dispatch()
    {
    }
    public function notification(Request $request, $udid)
    {
        $notification = Notification::all()->where('udid', $udid);
        return response()->json($notification[0]);
    }
    public function deliver()
    {
    }
}
