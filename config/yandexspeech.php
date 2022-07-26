<?php

return [

    'tokens' => [
        'api' => env('YANDEX_SPEECH_API_TOKEN', '')
    ],

    'filesystem' => [
        'save_files' => env('YANDEX_SPEECH_SAVE_FILES', false),
        'path' => env('YANDEX_SPEECH_PATH_FILES', 'yandex_speech/audio')
    ],
];