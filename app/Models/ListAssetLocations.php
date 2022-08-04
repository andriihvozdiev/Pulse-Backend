<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListAssetLocations extends Model
{
  	protected $table = 'ListAssetLocations';
  	public $timestamps = false;
  	protected $primaryKey = 'PropNum';
	public $incrementing = false;
}