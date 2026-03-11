<?php

namespace App\Http\Requests;

use App\Providers\FortifyServiceProvider;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Fortify;

class SendPasswordResetLinkRequest extends \Laravel\Fortify\Http\Requests\SendPasswordResetLinkRequest
{
    public function rules(): array
    {
        return [
            Fortify::email() => [
                'required',
                'email',
                Rule::in(FortifyServiceProvider::ALLOWED_EMAILS),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            Fortify::email() . '.in' => 'Resetiranje lozinke nije dozvoljeno za ovu email adresu.',
        ];
    }
}
