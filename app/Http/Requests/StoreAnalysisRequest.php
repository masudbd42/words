<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'keywords' => ['required', 'string', 'max:2000'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public function keywords(): array
    {
        return collect(explode(',', $this->input('keywords', '')))
            ->map(static fn (string $keyword) => trim($keyword))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (count($this->keywords()) === 0) {
                $validator->errors()->add('keywords', 'Please provide at least one keyword.');
            }
        });
    }
}
