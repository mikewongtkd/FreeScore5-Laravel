<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;
	protected $fillable = [ 'code', 'description', 'criteria', 'info' ];
	public $incrementing = false;

	public function athletes() {
		return $this->belongsToMany( Athlete::class );
	}

	public function difficulty() {
		if( ! isset( $this->info )) { return null; }
		$info = json_decode( $this->info );
		if( ! isset( $info->difficulty )) { return null; }
		return $info->difficulty;
	}

	public function head_contact_rules() {
		if( ! property_exists( $this, 'info' )) { return null; }
		$info = json_decode( $this->info );
		if( ! property_exists( $info, 'headcontactrules' )) { return null; }
		return $info->headcontactrules;
	}

	public function matches() {
		return $this->belongsToMany( \App\Models\Match::class );
	}

}
