<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpsSchedule extends Model
{
  protected $table = 'tblOpsSchedule';
  public $timestamps = false;
  protected $primaryKey = 'ScheduleID';
}