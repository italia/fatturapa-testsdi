<?php

namespace FatturaPa\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    public $incrementing = false;
	public $timestamps = false;
    protected $primaryKey = 'cedente';
    protected $table = 'channels';
    protected $fillable = [
        'cedente',
        'issuer'
    ];
	
	/*public function parent()
    {
        return $this->belongsTo('Channel', 'cedente');
    }

    public function children()
    {
        return $this->hasMany('Channel', 'cedente');
    }*/

	
}
