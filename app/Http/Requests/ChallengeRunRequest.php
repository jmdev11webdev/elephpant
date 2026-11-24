<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChallengeRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'language' => ['sometimes', 'string', 'in:php,typescript,sql'],
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', 'string', 'max:65536'],
            'decision_choice' => ['nullable', 'string', 'max:120'],
        ];
    }
}
