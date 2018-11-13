<?php

namespace FatturaPa\Control;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use FatturaPa\Core\Models\Actor;
use FatturaPa\Core\Actors\Base;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class ActorsController extends Controller
{
  public function create()
  {
      //
  }
  
  public function store()
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
				$actors = new Actor;
		        $actors->code   = Input::get('code');
		        $actors->id     = Input::get('id');
		        $actors->save();
		    } catch (\Exception $e) {
		        abort(400, $e->getMessage());
		        exit;
		    }
		
		    return response()->json(array('actors' => $actors));
		}
  }
  
  public function show($id)
  {
      //
  }
  
  public function edit($id)
  {
      //
  }
 
  public function update($id)
  {
       //
  }

  public function destroy($id)
  {
      $actors = Actor::find($id);
      $actors->delete();
	  return response()->json(array('sucess' => 'true'));
	  
  }
}
