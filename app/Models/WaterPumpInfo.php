<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaterPumpInfo extends Model
{
  	protected $table = 'tblWaterPumpInfo';
  	public $timestamps = false;
  	protected $primaryKey = 'PumpID';	
}