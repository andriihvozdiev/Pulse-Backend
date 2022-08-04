<?php

namespace App\Models\WBD;

use Illuminate\Database\Eloquent\Model;

class WBDPumps extends Model
{
  protected $table = 'WBDPumps';
  public $timestamps = false;
  protected $primaryKey = 'PumpID';
}