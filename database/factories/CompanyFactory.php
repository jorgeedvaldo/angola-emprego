<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition()
    {
        $name = fake()->unique()->company();

        return [
            'user_id' => User::factory()->company(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numerify('###'),
            'description' => fake()->paragraph(),
            'location' => 'Luanda',
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('9########'),
            'theme_color' => '#2557A7',
            'max_attachments' => 1,
            'approval_status' => 'approved',
            'approved_at' => now(),
        ];
    }
}
