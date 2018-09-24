<?php

namespace FatturaPa\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $table = 'invoices';
    protected $fillable = ['remote_id', 'nomefile','posizione','cedente','anno','status','blob','ctime','actor','issuer'];
}
