<?php

namespace App\Models\WBD;

use Illuminate\Database\Eloquent\Model;

class WBDSurveys extends Model
{
  protected $table = 'WBDSurveys';
  public $timestamps = false;
  protected $primaryKey = 'SurveyPointID';
}