<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterMeterData extends Model
{
  protected $table = 'WaterMeterData';
  public $timestamps = false;
  protected $primaryKey = 'WMDID';
}