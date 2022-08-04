<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListMeterProblem extends Model
{
  	protected $table = 'ListMeterProblem';
  	public $timestamps = false;
  	protected $primaryKey = 'ReasonCode';
  	public $incrementing = false;
}