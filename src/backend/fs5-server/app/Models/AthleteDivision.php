<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AthleteDivision extends Model
{
    use HasFactory;
	protected $table = 'athlete_division';

	public function athlete() {
		return $this->belongsTo( Athlete::class );
	}

	public function division() {
		return $this->belongsTo( Division::class );
	}
}
