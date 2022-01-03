<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DivisionFactory extends Factory
{
	protected $model = \App\Models\Division::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
	// ============================================================
    public function definition()
	// ============================================================
    {
		$entry = [
			'id'   => Str::uuid(),
			'code' => '',
			'description' => '',
			'description' => '',
			'criteria' => '{}',
			'info' => null
		];

		return $entry;
    }
}
