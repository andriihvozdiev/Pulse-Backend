<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
  	protected $table = 'Invoices';
  	public $timestamps = false;
  	protected $primaryKey = 'InvoiceID';
}