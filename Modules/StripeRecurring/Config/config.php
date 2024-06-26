<?php

return [
    'name' => 'StripeRecurring',

    'alias' => 'striperecurring',

    'logo' => 'Modules/StripeRecurring/Resources/assets/StripeRecurring.png',

    // Stripe recurring addon settings

    'options' => [
        ['label' => __('Settings'), 'type' => 'modal', 'url' => 'striperecurring.edit'],
        ['label' => __('Stripe Documentation'), 'target' => '_blank', 'url' => 'https://stripe.com/docs/keys']
    ],

    /**
     * Stripe data validation
     */
    'validation' => [
        'rules' => [
            'publishableKey' => 'required',
            'clientSecret' => 'required',
            'sandbox' => 'required',
        ],
        'attributes' => [
            'publishableKey' => __('Publishable Key'),
            'clientSecret' => __('Client Secret'),
            'sandbox' => __('Please specify sandbox enabled/disabled.')
        ]
    ],
    'fields' => [
        'publishableKey' => [
            'label' => __('Publishable Key'),
            'type' => 'text',
            'required' => true
        ],
        'clientSecret' => [
            'label' => __('Client Secret Key'),
            'type' => 'text',
            'required' => true
        ],
        'instruction' => [
            'label' => __('Instruction'),
            'type' => 'textarea',
        ],
        'sandbox' => [
            'label' => __('Sandbox'),
            'type' => 'select',
            'required' => true,
            'options' => [
                'Enabled' => 1,
                'Disabled' =>  0
            ]
        ],
        'status' => [
            'label' => __('Status'),
            'type' => 'select',
            'required' => true,
            'options' => [
                'Active' => 1,
                'Inactive' =>  0
            ]
        ]
    ],

    'store_route' => 'striperecurring.store',

];
