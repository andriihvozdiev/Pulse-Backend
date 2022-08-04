<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RunTickets extends Model
{
  protected $table = 'RunTickets';
  public $timestamps = false;
  protected $primaryKey = 'InternalTicketID';
}