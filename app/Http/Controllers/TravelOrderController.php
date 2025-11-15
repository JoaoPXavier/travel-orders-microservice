<?php

namespace App\Http\Controllers;

use App\Events\TravelOrderStatusUpdated;
use App\Http\Requests\TravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderStatusRequest;
use App\Models\TravelOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TravelOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Auth::user()->travelOrders();

            // Apply status filter if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Apply destination filter if provided
            if ($request->has('destination')) {
                $query->where('destination', 'like', '%' . $request->destination . '%');
            }

            // Apply date range filter if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('departure_date', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            $travelOrders = $query->latest()->get();

            return response()->json([
                'data' => $travelOrders,
                'count' => $travelOrders->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar pedidos de viagem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TravelOrderRequest $request): JsonResponse
    {
        try {
            $travelOrder = Auth::user()->travelOrders()->create($request->validated());

            return response()->json([
                'message' => 'Pedido de viagem criado com sucesso!',
                'data' => $travelOrder
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar pedido de viagem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TravelOrder $travelOrder): JsonResponse
    {
        try {
            // Verify the travel order belongs to the authenticated user
            if ($travelOrder->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Acesso não autorizado.'
                ], 403);
            }

            return response()->json([
                'data' => $travelOrder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar pedido de viagem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TravelOrderRequest $request, TravelOrder $travelOrder): JsonResponse
    {
        try {
            // Verify the travel order belongs to the authenticated user
            if ($travelOrder->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Acesso não autorizado.'
                ], 403);
            }

            // Prevent updates if status is approved or cancelled
            if (!$travelOrder->canBeUpdated()) {
                return response()->json([
                    'message' => 'Não é possível atualizar um pedido com status ' . $travelOrder->status . '.'
                ], 422);
            }

            $travelOrder->update($request->validated());

            return response()->json([
                'message' => 'Pedido de viagem atualizado com sucesso!',
                'data' => $travelOrder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar pedido de viagem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TravelOrder $travelOrder): JsonResponse
    {
        try {
            // Verify the travel order belongs to the authenticated user
            if ($travelOrder->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Acesso não autorizado.'
                ], 403);
            }

            // Business rule: Cannot cancel approved travel orders
            if (!$travelOrder->canBeCancelled()) {
                return response()->json([
                    'message' => 'Não é possível cancelar um pedido aprovado.'
                ], 422);
            }

            $travelOrder->delete();

            return response()->json([
                'message' => 'Pedido de viagem cancelado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao cancelar pedido de viagem.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update travel order status (for approvers)
     */
    public function updateStatus(UpdateTravelOrderStatusRequest $request, TravelOrder $travelOrder): JsonResponse
    {
        try {
            // Verify the user is NOT the owner of the travel order
            // Approvers cannot be the same user who requested the travel
            if ($travelOrder->user_id === Auth::id()) {
                return response()->json([
                    'message' => 'Você não pode alterar o status do seu próprio pedido.'
                ], 403);
            }

            $previousStatus = $travelOrder->status;
            $travelOrder->update(['status' => $request->status]);

            // Dispatch event for notifications - SYSTEM ACTIVE
            event(new TravelOrderStatusUpdated($travelOrder, Auth::id(), $previousStatus));

            return response()->json([
                'message' => 'Status do pedido atualizado com sucesso!',
                'data' => $travelOrder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar status do pedido.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}