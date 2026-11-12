<?php

return [
    'sumopod' => [
        'label' => 'SumoPod AI',
        'base_url' => 'https://ai.sumopod.com/v1',
        'default_model' => 'gpt-4o-mini',
        'hint' => 'Daftar atau masuk di ai.sumopod.com, buat API Key di menu API Keys.',
    ],
    'openai' => [
        'label' => 'OpenAI',
        'base_url' => 'https://api.openai.com/v1',
        'default_model' => 'gpt-4o-mini',
        'hint' => 'Buat API Key di platform.openai.com.',
    ],
    'groq' => [
        'label' => 'Groq',
        'base_url' => 'https://api.groq.com/openai/v1',
        'default_model' => 'llama-3.3-70b-versatile',
        'hint' => 'Buat API Key di console.groq.com.',
    ],
    'deepseek' => [
        'label' => 'DeepSeek',
        'base_url' => 'https://api.deepseek.com/v1',
        'default_model' => 'deepseek-chat',
        'hint' => 'Buat API Key di platform.deepseek.com.',
    ],
    'openrouter' => [
        'label' => 'OpenRouter',
        'base_url' => 'https://openrouter.ai/api/v1',
        'default_model' => 'openai/gpt-4o-mini',
        'hint' => 'Buat API Key di openrouter.ai.',
    ],
    'custom' => [
        'label' => 'Custom (OpenAI-compatible)',
        'base_url' => null,
        'default_model' => null,
        'hint' => 'Masukkan Base URL endpoint OpenAI-compatible (mis. LiteLLM, proxy internal).',
    ],
];
