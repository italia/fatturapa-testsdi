<?php

namespace FatturaPa\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    public $incrementing = false;
	public $timestamps = false;
    protected $primaryKey = 'id';
    protected $table = 'actors';
    protected $fillable = [
        'id',
        'code'
    ];
}
