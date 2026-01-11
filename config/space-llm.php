<?php

return [
    'url' => env('SPACE_LLM_URL'),
    'timeout' => env('SPACE_LLM_TIMEOUT', 120),
    'temperature' => env('SPACE_LLM_TEMPERATURE', 0.7),
];
