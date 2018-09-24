<?php

namespace FatturaPa\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $table = 'notifications';
    protected $fillable = ['invoice_id','type','status','blob','actor','nomefile','ctime'];
}
