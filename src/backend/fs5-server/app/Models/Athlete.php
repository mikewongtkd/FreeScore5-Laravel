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
		return $this->belongsToMany( Division::class );
	}

	public function matches() {
		return $this->belongsToMany( \App\Models\Match::class );
	}

}
