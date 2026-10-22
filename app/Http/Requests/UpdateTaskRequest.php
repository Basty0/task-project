<?php

namespace App\Http\Requests;

use App\Enums\Stage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('ticket')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stage' => ['sometimes', 'string', Rule::enum(Stage::class)],
            'position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
