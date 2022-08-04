<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListCompany extends Model
{
  protected $table = 'ListCompany';
  public $timestamps = false;
  protected $primaryKey = 'CompanyCode';
  public $incrementing = false;
}