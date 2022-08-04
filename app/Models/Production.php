<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
  protected $table = 'tblProduction';
  public $timestamps = false;
  protected $primaryKey = 'ProdID';

}