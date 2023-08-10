<?php

declare(strict_types=1);

namespace TimAlexander\Aisearch\Search;

use TimAlexander\Aisearch\ChatGPT\ChatGPT;
use TimAlexander\Aisearch\SystemConfig\SystemConfig;

class Search
{
    private array $results = [];

    public function __construct(
        private SystemConfig $systemConfig,
        public readonly string $query,
    ) {
    }

    public function executeSearch(): void
    {
        $this->results = [];

        $uniqid = uniqid('', true);

        $chatgpt = new ChatGPT($this->systemConfig, $uniqid);

        $responseText = $chatgpt->call($this->query);

        $secondQuery = $this->createSecondQuery($responseText);

        $secondResponseText = $chatgpt->call($secondQuery);

        $this->results = $this->createResults($secondResponseText);
    }

    private function createSecondQuery(string $responseText): string
    {
        $fileContents = $this->readFileContents($responseText);

        $job = <<<TEXT
I have found the following files and their contents for you. Please choose which (at the most 3) files represent the search best by only returning the file name in full path.
TEXT;

        return sprintf('%s: %s', $job, $fileContents);
    }

    private function readFileContents(string $responseText): string
    {
        $responseTextArray = json_decode($responseText, true);

        $fileContents = '';

        foreach ($responseTextArray as $result) {
            $fileContents .= 'File "' . $result . '":' . PHP_EOL;
            $fileContents .= 'SOF>>' . PHP_EOL;
            $fileContents .= file_get_contents($result);
            $fileContents .= '<<EOF' . PHP_EOL;
        }

        return $fileContents;
    }

    private function createResults(string $responseText): array
    {
        return json_decode($responseText);
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
