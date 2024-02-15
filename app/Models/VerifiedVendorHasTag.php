<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifiedVendorHasTag extends Model
{

	protected $table = 'verified_vendors_has_tags';

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
