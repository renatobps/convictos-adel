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
        // Aceita MP_* (padrão atual) e MERCADOPAGO_* (legado).
        'access_token' => env('MP_ACCESS_TOKEN', env('MERCADOPAGO_ACCESS_TOKEN')),
        'public_key' => env('MP_PUBLIC_KEY', env('MERCADOPAGO_PUBLIC_KEY')),
        'sandbox' => filter_var(env('MP_SANDBOX', env('MERCADOPAGO_SANDBOX', false)), FILTER_VALIDATE_BOOLEAN),
        'webhook_secret' => env('MP_WEBHOOK_SECRET', env('MERCADOPAGO_WEBHOOK_SECRET')),
        'statement_descriptor' => env('MP_STATEMENT_DESCRIPTOR', env('MERCADOPAGO_STATEMENT_DESCRIPTOR', 'CONVICTOS')),
        'currency' => env('MP_CURRENCY', env('MERCADOPAGO_CURRENCY', 'BRL')),
        // URL pública do webhook (ngrok/produção). Se vazio, usa a rota local.
        'notification_url' => env('MP_NOTIFICATION_URL', env('MERCADOPAGO_NOTIFICATION_URL')),
    ],

    'loja' => [
        'whatsapp' => env('LOJA_WHATSAPP', '5561900000000'),
        'email_admin' => env('MAIL_ADMIN_ADDRESS', env('MAIL_FROM_ADDRESS')),
    ],

    'evolution_api' => [
        'base_url' => env('WHATSAPP_API_URL', env('EVOLUTION_API_BASE_URL')),
        'instance_name' => env('WHATSAPP_INSTANCE_NAME', env('EVOLUTION_API_INSTANCE_NAME')),
        'api_key' => env('WHATSAPP_API_KEY', env('EVOLUTION_API_KEY')),
        // Evolution GO (whatsmeow) — ver https://evogo.arkcoredev.com/swagger/index.html
        'text_endpoint' => env('EVOLUTION_API_TEXT_ENDPOINT', '/send/text'),
        'media_endpoint' => env('EVOLUTION_API_MEDIA_ENDPOINT', '/send/media'),
        'buttons_endpoint' => env('EVOLUTION_API_BUTTONS_ENDPOINT', '/send/button'),
        'location_endpoint' => env('EVOLUTION_API_LOCATION_ENDPOINT', '/send/location'),
        'poll_endpoint' => env('EVOLUTION_API_POLL_ENDPOINT', '/send/poll'),
        'enquete_footer' => env('EVOLUTION_API_ENQUETE_FOOTER', 'CONVICTOS UM 2027'),
        'pos_inscricao_image_url' => env('EVOLUTION_API_POS_INSCRICAO_IMAGE_URL'),
        'webhook_url' => env('WHATSAPP_WEBHOOK_URL', env('EVOLUTION_WEBHOOK_URL')),
    ],

    'enquete' => [
        'agradecimento' => env('ENQUETE_AGRADECIMENTO_MSG', 'Obrigado pela sua resposta! ✅ Registramos: *{resposta}*'),
    ],

];
