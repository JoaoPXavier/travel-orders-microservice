<?php

namespace Tests\Feature;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TravelOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = auth()->login($this->user);
    }

    /**
     * Test user can create a travel order.
     */
    public function test_user_can_create_travel_order(): void
    {
        $travelOrderData = [
            'order_id' => 'ORDER-001',
            'applicant_name' => 'John Doe',
            'destination' => 'São Paulo',
            'departure_date' => '2025-12-01',
            'return_date' => '2025-12-05',
            'status' => 'solicitado'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/travel-orders', $travelOrderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'order_id',
                    'applicant_name',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status',
                    'user_id',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'message' => 'Pedido de viagem criado com sucesso!',
                'data' => [
                    'order_id' => 'ORDER-001',
                    'applicant_name' => 'John Doe',
                    'destination' => 'São Paulo',
                    'status' => 'solicitado'
                ]
            ]);

        $this->assertDatabaseHas('travel_orders', [
            'order_id' => 'ORDER-001',
            'user_id' => $this->user->id
        ]);
    }

    /**
     * Test travel order creation validation errors.
     */
    public function test_travel_order_creation_validation_errors(): void
    {
        // Test empty data
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/travel-orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'order_id', 
                'applicant_name', 
                'destination', 
                'departure_date', 
                'return_date'
            ]);

        // Test invalid dates
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/travel-orders', [
            'order_id' => 'ORDER-001',
            'applicant_name' => 'John Doe',
            'destination' => 'São Paulo',
            'departure_date' => '2025-12-10',
            'return_date' => '2025-12-05', // return before departure
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['return_date']);
    }

    /**
     * Test user can list their travel orders.
     */
    public function test_user_can_list_their_travel_orders(): void
    {
        TravelOrder::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/travel-orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'count'
            ])
            ->assertJson([
                'count' => 3
            ]);
    }

    /**
     * Test user can filter travel orders by status.
     */
    public function test_user_can_filter_travel_orders_by_status(): void
    {
        TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'solicitado'
        ]);

        TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'aprovado'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/travel-orders?status=aprovado');

        $response->assertStatus(200)
            ->assertJson([
                'count' => 1
            ]);
    }

    /**
     * Test user can view their travel order.
     */
    public function test_user_can_view_their_travel_order(): void
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/travel-orders/{$travelOrder->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_id',
                    'applicant_name',
                    'destination',
                    'departure_date',
                    'return_date',
                    'status'
                ]
            ]);
    }

    /**
     * Test user cannot view other users travel orders.
     */
    public function test_user_cannot_view_other_users_travel_orders(): void
    {
        $otherUser = User::factory()->create();
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/travel-orders/{$travelOrder->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Acesso não autorizado.'
            ]);
    }

    /**
     * Test user can update their travel order.
     */
    public function test_user_can_update_their_travel_order(): void
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'solicitado'
        ]);

        $updateData = [
            'destination' => 'Rio de Janeiro',
            'applicant_name' => 'Jane Doe'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/travel-orders/{$travelOrder->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pedido de viagem atualizado com sucesso!',
                'data' => [
                    'destination' => 'Rio de Janeiro',
                    'applicant_name' => 'Jane Doe'
                ]
            ]);
    }

    /**
     * Test user cannot update approved travel order.
     */
    public function test_user_cannot_update_approved_travel_order(): void
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'aprovado'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/travel-orders/{$travelOrder->id}", [
            'destination' => 'Rio de Janeiro'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Não é possível atualizar um pedido com status aprovado.'
            ]);
    }

    /**
     * Test user can delete their travel order.
     */
    public function test_user_can_delete_their_travel_order(): void
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'solicitado'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/travel-orders/{$travelOrder->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pedido de viagem cancelado com sucesso!'
            ]);

        $this->assertDatabaseMissing('travel_orders', [
            'id' => $travelOrder->id
        ]);
    }

    /**
     * Test business rule: cannot cancel approved travel order.
     */
    public function test_user_cannot_cancel_approved_travel_order(): void
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'aprovado'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/travel-orders/{$travelOrder->id}");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Não é possível cancelar um pedido aprovado.'
            ]);
    }

    /**
     * Test status update by different user.
     */
    public function test_other_user_can_update_travel_order_status(): void
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'solicitado'
        ]);

        $approver = User::factory()->create();
        $approverToken = auth()->login($approver);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $approverToken,
        ])->patchJson("/api/travel-orders/{$travelOrder->id}/status", [
            'status' => 'aprovado'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Status do pedido atualizado com sucesso!',
                'data' => [
                    'status' => 'aprovado'
                ]
            ]);
    }

    /**
     * Test user cannot update status of their own travel order.
     */
    public function test_user_cannot_update_status_of_their_own_travel_order(): void
    {
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'solicitado'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/travel-orders/{$travelOrder->id}/status", [
            'status' => 'aprovado'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Você não pode alterar o status do seu próprio pedido.'
            ]);
    }
}