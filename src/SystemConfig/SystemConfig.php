<?php

declare(strict_types=1);

namespace TimAlexander\Aisearch\SystemConfig;

class SystemConfig
{
    private array $config;

    public function __construct() {
        $this->load();
    }

    public function load(): void
    {
        $file = __DIR__ . '/../../config/config.json';

        if (!file_exists($file)) {
            file_put_contents($file, '{}');
        }

        $this->config = json_decode(file_get_contents($file), true);
    }

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
        $this->save();
    }

    public function isset(string $key): bool
    {
        return isset($this->config[$key]);
    }

    public function get(string $key, mixed $defaultValue = null): mixed
    {
        if (!$this->isset($key)) {
            if ($defaultValue !== null) {
                $this->set($key, $defaultValue);
                return $defaultValue;
            }

            return null;
        }

        return $this->config[$key];
    }

    public function save(): void
    {
        file_put_contents(__DIR__ . '/../../config/config.json', json_encode($this->config, JSON_PRETTY_PRINT));
    }
}
