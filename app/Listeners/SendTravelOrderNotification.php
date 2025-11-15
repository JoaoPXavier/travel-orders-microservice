<?php

namespace App\Listeners;

use App\Events\TravelOrderStatusUpdated;
use App\Models\User;
use App\Notifications\TravelOrderStatusChanged;
use Illuminate\Support\Facades\Log;

class SendTravelOrderNotification
{
    /**
     * Handle the event.
     *
     * @param \App\Events\TravelOrderStatusUpdated $event
     * @return void
     */
    public function handle(TravelOrderStatusUpdated $event): void
    {
        try {
            // Get the user who owns the travel order
            $travelOrderOwner = User::find($event->travelOrder->user_id);

            if (!$travelOrderOwner) {
                Log::warning('Travel order owner not found - notification skipped', [
                    'travel_order_id' => $event->travelOrder->id
                ]);
                return;
            }

            // Get the user who updated the status
            $updatedByUser = User::find($event->updatedByUserId);

            if (!$updatedByUser) {
                Log::warning('User who updated status not found - notification skipped', [
                    'user_id' => $event->updatedByUserId
                ]);
                return;
            }

            // In testing environment, we'll log instead of sending real notifications
            if (app()->environment('testing')) {
                Log::info('TRAVEL ORDER NOTIFICATION SIMULATED - Testing Environment', [
                    'travel_order_id' => $event->travelOrder->id,
                    'order_id' => $event->travelOrder->order_id,
                    'recipient' => $travelOrderOwner->email,
                    'previous_status' => $event->previousStatus,
                    'new_status' => $event->travelOrder->status,
                    'updated_by' => $updatedByUser->name,
                    'environment' => 'testing'
                ]);
                
                // Store notification in database for testing verification
                $travelOrderOwner->notifications()->create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => TravelOrderStatusChanged::class,
                    'data' => [
                        'travel_order_id' => $event->travelOrder->id,
                        'order_id' => $event->travelOrder->order_id,
                        'previous_status' => $event->previousStatus,
                        'new_status' => $event->travelOrder->status,
                        'updated_by_user_id' => $updatedByUser->id,
                        'updated_by_user_name' => $updatedByUser->name,
                        'message' => "Seu pedido de viagem {$event->travelOrder->order_id} foi {$event->travelOrder->status}.",
                        'action_url' => "/travel-orders/{$event->travelOrder->id}",
                        'timestamp' => now()->toISOString(),
                        'environment' => 'testing_simulation'
                    ],
                    'read_at' => null,
                ]);
                
                return;
            }

            // Production environment - send real notification
            $travelOrderOwner->notify(new TravelOrderStatusChanged(
                $event->travelOrder,
                $updatedByUser,
                $event->previousStatus
            ));

            Log::info('Travel order status notification sent successfully', [
                'travel_order_id' => $event->travelOrder->id,
                'recipient_id' => $travelOrderOwner->id,
                'status' => $event->travelOrder->status
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send travel order notification', [
                'travel_order_id' => $event->travelOrder->id,
                'error_message' => $e->getMessage(),
                'environment' => app()->environment()
            ]);
        }
    }
}