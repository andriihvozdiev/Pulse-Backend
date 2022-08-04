<?php

namespace App\Models\RigReportsModels;

use Illuminate\Database\Eloquent\Model;

class RigReports extends Model
{
  	protected $table = 'tblRigReports';
  	public $timestamps = false;
  	protected $primaryKey = 'ReportID';

  	protected $appends = ['rigImages'];

	public function getRigImagesAttribute() {
		return $this->hasMany('App\Models\RigReportsModels\RigReportsImage', 'ReportID', 'ReportID')->select('Image', 'ImageName', 'ImageID')->get();
	}
}