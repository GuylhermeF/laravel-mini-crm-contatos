<?php

declare(strict_types=1);

namespace Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateContactRequest extends FormRequest
{
    use ContactFormRules;

    public function authorize(): bool
    {
        return true;
    }
}
