<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePushSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Nama field mengikuti objek PushSubscription browser.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Endpoint adalah URL push service milik browser. Tolak HTTP dan
            // host internal agar worker tidak bisa dipakai sebagai SSRF.
            'endpoint' => [
                'required',
                'string',
                'url:https',
                'max:1024',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $host = parse_url((string) $value, PHP_URL_HOST);

                    if (! is_string($host) || $host === '') {
                        $fail('Endpoint push tidak valid.');

                        return;
                    }

                    $host = strtolower($host);

                    if (
                        filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) !== false
                        || $host === 'localhost'
                        || str_ends_with($host, '.localhost')
                        || str_ends_with($host, '.local')
                        || str_ends_with($host, '.internal')
                    ) {
                        $fail('Endpoint push tidak valid.');
                    }
                },
            ],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'content_encoding' => ['nullable', 'string', 'in:aesgcm,aes128gcm'],
        ];
    }
}
