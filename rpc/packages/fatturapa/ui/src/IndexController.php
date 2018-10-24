<?php

namespace FatturaPa\Ui;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FatturaPa\Core\Models\Invoice;
use FatturaPa\Core\Models\Notification;
use FatturaPa\Core\Actors\Base;

class IndexController extends Controller
{
    
    public function index(Request $request)
    {    	   
		return view('ui::index',['actors' => Base::getActors()]);	
    }
	public function sdi(Request $request)
    {    	
		return view('ui::sdi',['actors' => Base::getActors()]);	
    }
    public function td(Request $request ,$id)
    {    	    
		return view('ui::td', ['actor' => $id,'actors' => Base::getActors()]);	
    }
}
