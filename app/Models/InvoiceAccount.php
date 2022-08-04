<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAccount extends Model
{
  	protected $table = 'tblInvoiceAccount';
  	public $timestamps = false;
  	protected $primaryKey = 'AcctID';
}