<?php

declare(strict_types=1);

namespace TimAlexander\Aisearch\ChatGPT;

use TimAlexander\Aisearch\SystemConfig\SystemConfig;

class ChatGPT
{
    public string $model = 'gpt-3.5-turbo-0301';
    public string $url = 'https://api.openapi.com/v1/chat/completions';

    private string $chatPersistenceId;

    public function __construct(
        private SystemConfig $systemConfig
    ) {
        $this->chatPersistenceId = uniqid("", true);
    }

    public function call(
        $text
    ): string {
        $rules = 
    }
}
