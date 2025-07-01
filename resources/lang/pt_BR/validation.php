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
            'invalid' => 'O campo :attribute não é um CNPJ válido.',
            'size' => 'O campo :attribute deve ter 14 dígitos para ser um CNPJ válido.',
        ],
        'cpf' => [
            'invalid' => 'O campo :attribute não é um CPF válido.',
            'size' => 'O campo :attribute deve ter 11 dígitos para ser um CPF válido.',
        ],
        'generic' => [
            'invalid' => 'O campo :attribute não é um CNPJ ou CPF válido.',
            'size' => 'O campo :attribute deve ter 11 dígitos para CPF ou 14 dígitos para CNPJ.',
        ],
    ],
    'phone' => [
        'and' => 'e',
        'multiple' => 'O campo :attribute não é um telefone válido (:available).',
        'generic' => [
            'invalid' => 'O campo :attribute não é um telefone válido.',
            'label' => 'telefone',
        ],
        'landline' => [
            'invalid' => 'O campo :attribute não é um telefone fixo válido.',
            'label' => 'telefone fixo',
        ],
        'local_fare' => [
            'invalid' => 'O campo :attribute não é um telefone de tarifa local válido.',
            'label' => 'telefone de tarifa local',
        ],
        'mobile' => [
            'invalid' => 'O campo :attribute não é um celular válido.',
            'label' => 'celular',
        ],
        'non_regional' => [
            'invalid' => 'O campo :attribute não é um telefone não regional válido.',
            'label' => 'telefone não regional',
        ],
        'public_services' => [
            'invalid' => 'O campo :attribute não é um telefone de serviços públicos válido.',
            'label' => 'telefone de serviços públicos',
        ],
    ],

];
