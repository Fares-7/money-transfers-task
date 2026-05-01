<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferMoneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_account_id'      => ['required', 'uuid', 'exists:accounts,id'],
            'destination_account_id' => ['required', 'uuid', 'exists:accounts,id', 'different:source_account_id'],
            'amount'                 => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
