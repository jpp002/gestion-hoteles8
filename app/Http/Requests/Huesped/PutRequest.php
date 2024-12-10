<?php

namespace App\Http\Requests\Huesped;

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

        $huespedId = $this->route('huesped');

        $method = $this->method();

        if ($method == "PUT") {
            return [
                'nombre' => 'required|max:50',
                'apellido' => 'required|max:50',
                'dniPasaporte' => 'required|min:9|max:9|unique:huespedes,dniPasaporte' . $huespedId,
            ];
        } else {
            return [
                'nombre' => 'sometimes|max:50',
                'apellido' => 'sometimes|max:50',
                'dniPasaporte' => 'sometimes|min:9|max:9|unique:huespedes,dniPasaporte' . $huespedId,
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