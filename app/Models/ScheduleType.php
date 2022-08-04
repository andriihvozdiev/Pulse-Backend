<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleType extends Model
{
  protected $table = 'tblScheduleType';
  public $timestamps = false;
  protected $primaryKey = 'ScheduleTypeID';
}