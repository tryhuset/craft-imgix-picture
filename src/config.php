<?php
return [
    // 'variableName' => 'craftImgixPicture', // override craft variable name

    // Imgix API key
    'apiKey'         => '',

    // Volume handles mapped to Imgix domains
    'imgixDomains'   => [],

    // Imgix signed URLs token
    'imgixSignedToken' => '',

    // Lazy load attribute prefix
    'lazyLoadPrefix' => '',

    // 'options' => [], // Default imgix options
    'imageStyles' => [
        'complex-picture' => [
            // 'options' => [], // Override default imgix options
            'sources' => [
                [
                    'media' => '(max-width: 600px)',
                    'aspectRatio' => 4 / 3,
                    'sizes' => '100px',
                    'widths' => [100, 200],
                    // 'options' => [], // Override imgix options
                ],
                [
                    'media' => '(max-width: 1200px)',
                    'sizes' => '300px',
                    'widths' => [300, 600],
                ]
            ],
            'img' => [
                'aspectRatio' => 260 / 280,
                'sizes' => '50vw',
                'widths' => [260, 260 * 2, 260 * 3],
                'alt' => 'foo',
                'class' => 'foo', // Add your html attributes
                // 'options' => [], // Override imgix options
            ],
        ],

        'simple-image' => [
            'img' => [
                'widths' => [260],
                'alt' => 'foo',
            ],
        ]
    ],
];
