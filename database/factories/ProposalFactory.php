<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProposalOrigin;
use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Proposal> */
final class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'product' => fake()->words(3, true),
            'monthly_value' => fake()->randomFloat(2, 100, 10000),
            'status' => ProposalStatus::Draft,
            'origin' => fake()->randomElement(ProposalOrigin::cases()),
            'version' => 1,
        ];
    }

    public function submitted(): static
    {
        return $this->state(['status' => ProposalStatus::Submitted]);
    }

    public function approved(): static
    {
        return $this->state(['status' => ProposalStatus::Approved]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => ProposalStatus::Rejected]);
    }

    public function canceled(): static
    {
        return $this->state(['status' => ProposalStatus::Canceled]);
    }
}
