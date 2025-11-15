<?php

namespace App\Events;

use App\Models\TravelOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TravelOrderStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The travel order instance.
     *
     * @var \App\Models\TravelOrder
     */
    public $travelOrder;

    /**
     * The ID of user who performed the status update.
     *
     * @var int
     */
    public $updatedByUserId;

    /**
     * The previous status of the travel order.
     *
     * @var string
     */
    public $previousStatus;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\TravelOrder $travelOrder
     * @param int $updatedByUserId
     * @param string $previousStatus
     * @return void
     */
    public function __construct(TravelOrder $travelOrder, int $updatedByUserId, string $previousStatus)
    {
        $this->travelOrder = $travelOrder;
        $this->updatedByUserId = $updatedByUserId;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [];
    }
}