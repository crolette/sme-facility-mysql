<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NotDisposableEmail implements Rule
{
    protected $disposableDomains = [
        'mailinator.com',
        '10minutemail.com',
        'tempmail.com',
        'guerrillamail.com',
        'throwawaymail.com',
        'getnada.com',
        'nada.ltd',
        'trashmail.com',
        'maildrop.cc',
        'yopmail.com',
        'dispostable.com',
        'mintemail.com',
        'moakt.com',
        'emailondeck.com',
        'mytemp.email',
        'fakeinbox.com',
        'emailtemporario.com.br',
        'burnermail.io',
        'anonymbox.com',
        'mailcatch.com',
        'wow-resto.com'
    ];

    public function passes($attribute, $value)
    {
        $domain = strtolower(substr(strrchr($value, "@"), 1));
        return !in_array($domain, $this->disposableDomains);
    }

    public function message()
    {
        return 'Disposable email addresses are not allowed.';
    }
}
