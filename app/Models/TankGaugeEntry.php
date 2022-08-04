<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TankGaugeEntry extends Model
{
  protected $table = 'TankGaugeEntry';
  public $timestamps = false;
  protected $primaryKey = 'TankGaugeID';

}