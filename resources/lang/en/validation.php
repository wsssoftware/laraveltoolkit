<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laraveltoolkit Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'document' => [
        'cnpj' => [
            'invalid' => 'The :attribute field must be a valid CNPJ.',
            'size' => 'The :attribute field must have 14 digits to be a valid CNPJ.',
        ],
        'cpf' => [
            'invalid' => 'The :attribute field must be a valid CPF.',
            'size' => 'The :attribute field must have 11 digits to be a valid CPF.',
        ],
        'generic' => [
            'invalid' => 'The :attribute field must be a valid CNPJ or CPF.',
            'size' => 'The :attribute field must have 11 digits to be a valid CPF or 14 digits to be a valid CNPJ.',
        ],
    ],
    'phone' => [
        'and' => 'and',
        'multiple' => 'The :attribute field must be a valid phone (:available).',
        'generic' => [
            'invalid' => 'The :attribute field must be a valid phone.',
            'label' => 'phone',
        ],
        'landline' => [
            'invalid' => 'The :attribute field must be a valid landline phone.',
            'label' => 'landline phone',
        ],
        'local_fare' => [
            'invalid' => 'The :attribute field must be a valid local fare phone.',
            'label' => 'local fare phone',
        ],
        'mobile' => [
            'invalid' => 'The :attribute field must be a valid cellphone.',
            'label' => 'cellphone',
        ],
        'non_regional' => [
            'invalid' => 'The :attribute field mus be a valid non regional phone.',
            'label' => 'non regional phone',
        ],
        'public_services' => [
            'invalid' => 'The :attribute field must be a valid public services phone.',
            'label' => 'public services phone',
        ],
    ],

];
