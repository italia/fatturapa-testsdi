<?php

namespace FatturaPa\Control;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FatturaPa\Core\Models\Invoice;
use FatturaPa\Core\Models\Notification;
use FatturaPa\Core\Actors\Base;

class BaseController extends Controller
{
    
    public function index(Request $request)
    {
    }
    public function clear()
    {
        Base::clear();
        Notification::where('actor', '=', Base::getActor())->delete();
        Invoice::where('actor', '=', Base::getActor())->delete();
        echo "clear";
        exit;
    }
    public function setdatetime(Request $request)
    {
        $timestamp = $request->input('timestamp');
        if ($timestamp) {
            $datetime = new \DateTime($timestamp);
            if ($datetime) {
                Base::setDateTime($datetime);
                echo "timestamp: ".$timestamp;
                exit;
            } else {
                abort(400, "timestamp can not be converted to valid date time object");
            }
        } else {
            abort(400, "empty timestamp supplied");
        }
    }
    public function speed(Request $request)
    {
        $speed = $request->input('speed');
        if ($speed >= 0) {
            Base::setSpeed($speed);
            echo "speed: ".$speed;
            exit;
        } else {
            abort(400, "speed must be positive");
        }
    }
    public function getdatetime()
    {
        
        $dateTime=Base::getDateTime();
        
        echo "timestamp: " . $dateTime->date;
        exit;
    }
}
