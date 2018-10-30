<?php

namespace FatturaPa\Control;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FatturaPa\Core\Models\Channel;
use FatturaPa\Core\Actors\Base;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class ChannelsController extends Controller
{
	public function index()
	{
		$channels = Base::getChannels();
		return response()->json(array('channels' => $channels));
	}
	public function create()
	{
		//
	}

    public function store()
    {
    	
        // validate
       	$rules = array(
            'cedente'       => 'required',
            'id'      => 'required'
        );
				

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            abort(400, join(" | ", $errors->all()));
            exit;
        } else {
            try {
                // store
                $channel = new Channel;
                $channel->cedente       = Input::get('cedente');
                $channel->issuer      = Input::get('id');
                $channel->save();
            } catch (\Exception $e) {
                abort(400, $e->getMessage());
                exit;
            }

            return response()->json(array('channel' => $channel));
        }
    }

	public function show($id)
	{
		 $channel = Channel::find($id);
	}

	public function edit($id)
	{
		$channel = Channel::find($id);
	}

	public function update($id)
	{		
        // validate
        $rules = array(
            'code'       => 'required',
            'id'      => 'required'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            abort(400, join(" | ", $errors->all()));
            exit;
        } else {
            try {
                // store                
				$channel = Channel::find($id);
                $channel->cedente       = Input::get('code');
                $channel->issuer      = Input::get('id');
                $channel->save();
            } catch (\Exception $e) {
                abort(400, $e->getMessage());
                exit;
            }

            return response()->json(array('channel' => $channel));
        }
    }

	public function destroy($id)
	{
		 $channel = Channel::find($id);
         $channel->delete();
		 return response()->json(array('sucess' => 'true'));
		 
	}
}