<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveGameSlotRequest extends FormRequest
{
    /**
     * Valid Stack Region overworld zones and interiors.
     *
     * @var list<string>
     */
    public const VALID_ZONES = [
        'foundations-hub',
        'localhost-town',
        'algorithm-woods',
        'artisan-city',
        'legacy-harbor',
        'challenge-center',
        'starter-house',
        'logic-pavilion',
        'billing-guild',
        'security-warehouse',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'current_zone' => ['required', 'string', Rule::in(self::VALID_ZONES)],
            'player_x' => ['required', 'integer', 'min:0', 'max:200'],
            'player_y' => ['required', 'integer', 'min:0', 'max:200'],
            'facing' => ['required', 'string', Rule::in(['up', 'down', 'left', 'right'])],
            'playtime_seconds' => ['required', 'integer', 'min:0', 'max:36000000'],
            'label' => ['nullable', 'string', 'max:80'],
        ];
    }
}
