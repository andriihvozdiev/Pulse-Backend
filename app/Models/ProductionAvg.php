<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionAvg extends Model
{
  protected $table = 'tblProductionAvg';
  public $timestamps = false;
  protected $primaryKey = 'ProdID';
}