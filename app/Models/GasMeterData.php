<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GasMeterData extends Model
{
  protected $table = 'GasMeterData';
  public $timestamps = false;
  protected $primaryKey = 'DataID';
}