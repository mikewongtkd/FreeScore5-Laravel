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

	public function matches() {
		return $this->belongsToMany( \App\Models\Match::class );
	}

}
