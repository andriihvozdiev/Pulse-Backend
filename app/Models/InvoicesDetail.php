<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicesDetail extends Model
{
  protected $table = 'InvoicesDetail';
  public $timestamps = false;
  protected $primaryKey = 'ID';

  protected $appends = ['invoiceImages'];

  public function getInvoiceImagesAttribute() {
    return $this->hasMany('App\Models\InvoiceImage', 'InvoiceID', 'InvoiceID')->select('Image', 'ImageID', 'ImageName')->get();
  }

}