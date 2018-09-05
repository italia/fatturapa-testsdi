<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'uuid';
    public $timestamps = false;
    protected $table = 'invoices';
    protected $fillable = ['uuid','invoice_uuid','type','state','blob'];
}
