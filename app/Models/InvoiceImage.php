<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceImage extends Model
{
  	protected $table = 'tblInvoiceImages';
  	public $timestamps = false;
  	protected $primaryKey = 'ImageID';
}