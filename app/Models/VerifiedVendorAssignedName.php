<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifiedVendorAssignedName extends Model
{

	protected $table = 'verified_vendor_assigned_names';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [];

}
