<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mercadopago' => [
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'sandbox' => env('MERCADOPAGO_SANDBOX', true),
    ],

    'loja' => [
        'whatsapp' => env('LOJA_WHATSAPP', '5561900000000'),
        'email_admin' => env('MAIL_ADMIN_ADDRESS', env('MAIL_FROM_ADDRESS')),
    ],

    'evolution_api' => [
        'base_url' => env('WHATSAPP_API_URL', env('EVOLUTION_API_BASE_URL')),
        'instance_name' => env('WHATSAPP_INSTANCE_NAME', env('EVOLUTION_API_INSTANCE_NAME')),
        'api_key' => env('WHATSAPP_API_KEY', env('EVOLUTION_API_KEY')),
        'text_endpoint' => env('EVOLUTION_API_TEXT_ENDPOINT', '/message/sendText/{instance}'),
        'media_endpoint' => env('EVOLUTION_API_MEDIA_ENDPOINT', '/message/sendMedia/{instance}'),
        'buttons_endpoint' => env('EVOLUTION_API_BUTTONS_ENDPOINT', '/message/sendButtons/{instance}'),
        'location_endpoint' => env('EVOLUTION_API_LOCATION_ENDPOINT', '/message/sendLocation/{instance}'),
        'enquete_footer' => env('EVOLUTION_API_ENQUETE_FOOTER', 'CONVICTOS UM 2027'),
        'pos_inscricao_image_url' => env('EVOLUTION_API_POS_INSCRICAO_IMAGE_URL'),
        'webhook_url' => env('WHATSAPP_WEBHOOK_URL', env('EVOLUTION_WEBHOOK_URL')),
    ],

    'enquete' => [
        'agradecimento' => env('ENQUETE_AGRADECIMENTO_MSG', 'Obrigado pela sua resposta! ✅ Registramos: *{resposta}*'),
    ],

];
