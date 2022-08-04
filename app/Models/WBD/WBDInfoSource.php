<?php

namespace App\Models\WBD;

use Illuminate\Database\Eloquent\Model;

class WBDInfoSource extends Model
{
  protected $table = 'WBDInfoSource';
  public $timestamps = false;
  protected $primaryKey = 'SourceID';
}