<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'telegram' => [
        'bot_token'    => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
    ],

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],

    'openai' => [
        'api_key'      => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],

    'fal' => [
        'api_key' => env('FAL_API_KEY'),
        'base_url' => env('FAL_BASE_URL', 'https://fal.run'),
    ],

    'elevenlabs' => [
        'api_key'          => env('ELEVENLABS_API_KEY'),
        'base_url'         => env('ELEVENLABS_BASE_URL', 'https://api.elevenlabs.io/v1'),
        'stt_model'        => env('ELEVENLABS_STT_MODEL', 'scribe_v1'),
        'stt_language'     => env('ELEVENLABS_STT_LANGUAGE', 'tr'),
        'tts_model'        => env('ELEVENLABS_TTS_MODEL', 'eleven_multilingual_v2'),
        'default_voice_id' => env('ELEVENLABS_DEFAULT_VOICE_ID'),
        'timeout'          => (int) env('ELEVENLABS_TIMEOUT', 120),
    ],

    'netgsm' => [
        'usercode'          => env('NETGSM_USERCODE'),
        'password'          => env('NETGSM_PASSWORD'),
        'sender_id'         => env('NETGSM_SENDER_ID'),
        'default_audio_url' => env('NETGSM_DEFAULT_AUDIO_URL'),
        'default_number'    => env('NETGSM_DEFAULT_NUMBER'),
    ],

    'voice_agent' => [
        'shared_secret' => env('VOICE_AGENT_SHARED_SECRET'),
        'webhook_url'   => env('VOICE_AGENT_WEBHOOK_URL'),
    ],

    'sahibinden' => [
        'api_key'    => env('SAHIBINDEN_API_KEY'),
        'account_id' => env('SAHIBINDEN_ACCOUNT_ID'),
        'base_url'   => env('SAHIBINDEN_BASE_URL', 'https://api.sahibinden.com/v1'),
    ],

    'hepsiemlak' => [
        'api_key'    => env('HEPSIEMLAK_API_KEY'),
        'partner_id' => env('HEPSIEMLAK_PARTNER_ID'),
        'base_url'   => env('HEPSIEMLAK_BASE_URL', 'https://api.hepsiemlak.com/v2'),
    ],

    'emlakjet' => [
        'api_key'    => env('EMLAKJET_API_KEY'),
        'account_id' => env('EMLAKJET_ACCOUNT_ID'),
        'base_url'   => env('EMLAKJET_BASE_URL', 'https://api.emlakjet.com/v1'),
    ],

];
