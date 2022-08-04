<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionAvgField extends Model
{
  protected $table = 'tblProductionAvgField';
  public $timestamps = false;
  protected $primaryKey = 'ProdID';
}