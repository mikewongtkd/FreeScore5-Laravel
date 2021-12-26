<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Athlete extends Model
{
    use HasFactory;
	public $incrementing = false;

	public function divisions() {
		return $this->belongsTo( AthleteDivision::class )->division();
	}

	public function matches() {
		return $this->belongsTo( AthleteMatch::class )->match();
	}

	public function score( $thisMatch ) {
		$hasAthlete = $this->belongsTo( AthleteMatch::class );
		$hasMatch   = $thisMatch->belongsTo( AthleteMatch::class );
		$match      = $hasAthlete->intersect( $hasMatch );
		return $match->score();
	}

	public function scores() {
		return $this->matches()->score();
	}
}
