<?php

namespace Database\Factories;

use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition()
    {
        $title = fake()->jobTitle();

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numerify('####'),
            'company' => fake()->company(),
            'location' => 'Luanda',
            'description' => '<p>' . fake()->paragraphs(3, true) . '</p>',
            'email_or_link' => fake()->companyEmail(),
            'image' => null,
        ];
    }

    /**
     * Vaga de um país indicado pelo código ISO. Sem isto, a vaga fica de Angola,
     * que é o que o modelo faz a quem não escolhe país.
     */
    public function noPais(string $codigo)
    {
        return $this->state(fn () => [
            'country_id' => \App\Models\Country::query()->where('code', $codigo)->value('id'),
        ]);
    }
}
