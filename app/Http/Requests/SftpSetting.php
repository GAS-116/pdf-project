<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SftpSetting extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_uuid' => 'required|uuid',
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'required|string',
            'private_key' => 'required|string',
            'passphrase' => 'string|nullable',
            'root_path' => 'required|string',
        ];
    }
}
