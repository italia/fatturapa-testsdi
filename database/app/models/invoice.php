<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $primaryKey = 'uuid';
    public $timestamps = false;
    protected $table = 'invoices';
    protected $fillable = ['uuid','nomefile','posizione','cedente','anno','status','blob'];
}
