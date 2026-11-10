<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'approve' => ['required', 'boolean'],
            'comment' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(fn () => ! $this->boolean('approve')),
            ],
            'version' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'comment.required' => 'Для отказа нужен комментарий, автор должен понимать причину.',
            'version.required' => 'Не удалось определить версию заявки, обновите страницу.',
        ];
    }
}
