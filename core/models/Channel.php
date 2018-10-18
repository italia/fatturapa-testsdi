<?php

namespace FatturaPa\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'cedente';
    protected $table = 'channels';
    protected $fillable = [
        'cedente',
        'issuer'
    ];
}
