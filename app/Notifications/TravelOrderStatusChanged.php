<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelOrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The travel order instance.
     *
     * @var \App\Models\TravelOrder
     */
    public $travelOrder;

    /**
     * The user who updated the status.
     *
     * @var \App\Models\User
     */
    public $updatedBy;

    /**
     * The previous status of the travel order.
     *
     * @var string
     */
    public $previousStatus;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\TravelOrder $travelOrder
     * @param \App\Models\User $updatedBy
     * @param string $previousStatus
     * @return void
     */
    public function __construct(TravelOrder $travelOrder, User $updatedBy, string $previousStatus)
    {
        $this->travelOrder = $travelOrder;
        $this->updatedBy = $updatedBy;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusLabels = [
            'solicitado' => 'Solicitado',
            'aprovado' => 'Aprovado', 
            'cancelado' => 'Cancelado'
        ];

        $currentStatus = $statusLabels[$this->travelOrder->status] ?? $this->travelOrder->status;
        $previousStatus = $statusLabels[$this->previousStatus] ?? $this->previousStatus;

        return (new MailMessage)
            ->subject("Status do Pedido de Viagem Atualizado - {$this->travelOrder->order_id}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("O status do seu pedido de viagem foi atualizado.")
            ->line("**Pedido:** {$this->travelOrder->order_id}")
            ->line("**Destino:** {$this->travelOrder->destination}")
            ->line("**Status Anterior:** {$previousStatus}")
            ->line("**Novo Status:** {$currentStatus}")
            ->line("**Atualizado por:** {$this->updatedBy->name}")
            ->action('Ver Detalhes do Pedido', url("/travel-orders/{$this->travelOrder->id}"))
            ->line('Obrigado por usar nosso sistema!');
    }

    /**
     * Get the array representation for database storage.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'order_id' => $this->travelOrder->order_id,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->travelOrder->status,
            'updated_by_user_id' => $this->updatedBy->id,
            'updated_by_user_name' => $this->updatedBy->name,
            'message' => "Seu pedido de viagem {$this->travelOrder->order_id} foi {$this->getStatusMessage()}.",
            'action_url' => "/travel-orders/{$this->travelOrder->id}",
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get the status change message for database notification.
     *
     * @return string
     */
    private function getStatusMessage(): string
    {
        $messages = [
            'solicitado' => 'solicitado',
            'aprovado' => 'aprovado',
            'cancelado' => 'cancelado'
        ];

        return $messages[$this->travelOrder->status] ?? 'atualizado';
    }

    /**
     * Determine which queues should be used for each channel.
     *
     * @return array<string, string>
     */
    public function viaQueues(): array
    {
        return [
            'mail' => 'emails',
            'database' => 'notifications',
        ];
    }
}