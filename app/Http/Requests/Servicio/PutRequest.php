<?php

namespace App\Http\Requests\Servicio;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class PutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $method = $this->method();

        if ($method == "PUT") {
            return [
                'nombre' => 'required|min:3|max:50',
                'descripcion' => 'required|min:5|max:500',
                'categoria' => 'required|min:5',
            ];
        } else {
            return [
                'nombre' => 'sometimes|min:3|max:50',
                'descripcion' => 'sometimes|min:5|max:500',
                'categoria' => 'sometimes|min:5',
            ];
        }
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = new Response($validator->errors(), 400);
            throw new ValidationException($validator, $response);
        }
    }
}