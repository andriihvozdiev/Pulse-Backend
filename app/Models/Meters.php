<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meters extends Model
{
  protected $table = 'tblMeters';
  public $timestamps = false;
  protected $primaryKey = 'MeterID';
}