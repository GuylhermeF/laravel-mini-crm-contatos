<?php

declare(strict_types=1);

namespace Infrastructure\Http\Requests;

trait ContactFormRules
{
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:10', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'O nome é obrigatório.',
            'name.min'       => 'O nome deve ter pelo menos 2 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email'    => 'O e-mail deve ser um endereço válido.',
            'phone.required' => 'O telefone é obrigatório.',
            'phone.min'      => 'O telefone deve ter pelo menos 10 dígitos.',
        ];
    }
}
