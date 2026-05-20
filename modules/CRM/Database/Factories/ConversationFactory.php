<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Conversation;
use Modules\Core\Models\Office;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'office_id'    => Office::factory(),
            'channel'      => 'whatsapp',
            'status'       => 'open',
            'unread_count' => 0,
        ];
    }

    public function unread(int $count = 3): static
    {
        return $this->state(fn () => ['unread_count' => $count]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'archived']);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed']);
    }
}
