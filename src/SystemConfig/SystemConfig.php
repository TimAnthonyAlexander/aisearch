<?php

declare(strict_types=1);

namespace TimAlexander\Aisearch\SystemConfig;

class SystemConfig
{
    public array $config;

    public function __construct() {
        $this->load();
    }

    public function load(): void
    {
        $file = __DIR__ . '/../../config.json';
    }
}
