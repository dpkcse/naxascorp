<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Page> */
class PageFactory extends Factory
{
    protected $model = Page::class;
    public function definition(): array { $title = fake()->unique()->sentence(3); return ['title' => $title, 'slug' => fake()->unique()->slug(), 'template' => 'standard', 'status' => 'draft', 'display_order' => 1]; }
    public function published(): static { return $this->state(fn (): array => ['status' => 'published', 'published_at' => now()]); }
    public function archived(): static { return $this->state(fn (): array => ['status' => 'archived', 'archived_at' => now()]); }
}
