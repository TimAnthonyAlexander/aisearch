<?php

declare(strict_types=1);

namespace TimAlexander\Aisearch\ChatGPT;

use JsonException;
use TimAlexander\Aisearch\AIMessage\AIMessage;
use TimAlexander\Aisearch\SystemConfig\SystemConfig;

class ChatGPT
{
    public string $model = 'gpt-3.5-turbo-0301';
    public string $url = 'https://api.openapi.com/v1/chat/completions';

    private array $messages = [];

    public function __construct(
        private SystemConfig $systemConfig,
        private string $chatPersistenceId
    ) {
        $this->loadMessages();
    }

    private function loadMessages(): void
    {
        $file = __DIR__ . '/../../config/chat/' . $this->chatPersistenceId . '.json';

        if (!file_exists($file)) {
            file_put_contents($file, '{}');
        }

        $this->messages = json_decode(file_get_contents($file), true);
    }

    private function writeMessages(): void
    {
        file_put_contents(__DIR__ . '/../../config/chat/' . $this->chatPersistenceId . '.json', json_encode($this->messages));
    }

    public function call(
        string $text
    ): string {
        $rules = $this->systemConfig->get('rules', '');
        assert(is_string($rules));

        $rule = new AIMessage();
        $rule->role = 'system';
        $rule->content = $rules;

        $newMessage = new AIMessage();
        $newMessage->role = 'user';
        $newMessage->content = $text;

        $messages = array_merge([$rule], $this->messages, [$newMessage]);

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => 2000,
            'temperature' => 0.5,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
            'stop' => ['\r', '\n'],
        ];

        $jsonData = json_encode($data, JSON_THROW_ON_ERROR);

        $headers = [
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->systemConfig->get('openai_api_key', ''),
            ],
        ];

        $curl = curl_init();

        curl_setopt_array($curl, $headers);

        $response = curl_exec($curl);

        curl_close($curl);

        $response = trim((string) $response);

        try {
            $decodedResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_IGNORE);
        } catch (JsonException) {
            $decodedResponse = [];
        }

        if (!isset($decodedResponse['choices'][0]['message']['content'])) {
            return '';
        } else {
            $responseMessageText = $decodedResponse['choices'][0]['message']['content'];
        }

        $responseMessage = new AIMessage();
        $responseMessage->role = 'assistant';
        $responseMessage->content = $responseMessageText;

        $this->messages = array_merge($messages, [$responseMessage]);

        $this->writeMessages();

        return $responseMessageText;
    }
}
