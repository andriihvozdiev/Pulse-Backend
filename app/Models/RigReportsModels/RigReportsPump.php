<?php

namespace App\Models\RigReportsModels;

use Illuminate\Database\Eloquent\Model;

class RigReportsPump extends Model
{
  	protected $table = 'tblRigReportsPump';
  	public $timestamps = false;
  	protected $primaryKey = 'PumpID';
}