<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TravelOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'order_id' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                $isUpdate ? Rule::unique('travel_orders')->ignore($this->route('travel_order')) : 'unique:travel_orders,order_id'
            ],
            'applicant_name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255'
            ],
            'destination' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255'
            ],
            'departure_date' => [
                $isUpdate ? 'sometimes' : 'required',
                'date',
                'after_or_equal:today'
            ],
            'return_date' => [
                $isUpdate ? 'sometimes' : 'required',
                'date',
                'after:departure_date'
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::in(['solicitado', 'aprovado', 'cancelado'])
            ],
        ];

        return $rules;
    }

    /**
     * Get custom validation messages for specific rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_id.required' => 'O ID do pedido é obrigatório.',
            'order_id.unique' => 'Este ID do pedido já está em uso.',
            'order_id.string' => 'O ID do pedido deve ser um texto.',

            'applicant_name.required' => 'O nome do solicitante é obrigatório.',
            'applicant_name.string' => 'O nome do solicitante deve ser um texto.',
            'applicant_name.max' => 'O nome do solicitante não pode exceder 255 caracteres.',

            'destination.required' => 'O destino é obrigatório.',
            'destination.string' => 'O destino deve ser um texto.',
            'destination.max' => 'O destino não pode exceder 255 caracteres.',

            'departure_date.required' => 'A data de ida é obrigatória.',
            'departure_date.date' => 'A data de ida deve ser uma data válida.',
            'departure_date.after_or_equal' => 'A data de ida não pode ser anterior a hoje.',

            'return_date.required' => 'A data de volta é obrigatória.',
            'return_date.date' => 'A data de volta deve ser uma data válida.',
            'return_date.after' => 'A data de volta deve ser posterior à data de ida.',

            'status.string' => 'O status deve ser um texto.',
            'status.in' => 'O status deve ser: solicitado, aprovado ou cancelado.',
        ];
    }

    /**
     * Prepare data for validation - set default status if not provided.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->status ?? 'solicitado',
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'order_id' => 'ID do Pedido',
            'applicant_name' => 'Nome do Solicitante',
            'destination' => 'Destino',
            'departure_date' => 'Data de Ida',
            'return_date' => 'Data de Volta',
            'status' => 'Status',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'message' => 'Dados de entrada inválidos.',
            'errors' => $validator->errors()
        ], 422));
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        return response()->json([
            'message' => 'Dados de entrada inválidos.',
            'errors' => $errors
        ], 422);
    }
}