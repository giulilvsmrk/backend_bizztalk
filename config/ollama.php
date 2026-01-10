<?php

return [
    'url' => env('OLLAMA_URL', 'http://localhost:11434'),
    'model' => env('OLLAMA_MODEL', 'mistral'),
    'timeout' => (int) env('OLLAMA_TIMEOUT', 30),
    'temperature' => (float) env('OLLAMA_TEMPERATURE', 0.7),
];
