<?php

declare(strict_types=1);

namespace TimAlexander\Aisearch\Search;

use JsonException;
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

        $command = $chatgpt->call($this->createCommandQuery());

        $output = $this->executeCommand($command);

        $selectedFileJob = $this->createFileSelector($output);

        $secondResponseText = $chatgpt->call($selectedFileJob);

        $count = count(json_decode($secondResponseText ?? '[]'));
        print "AI selected $count files." . PHP_EOL;

        $this->results = $this->createResults($secondResponseText);
    }

    private function executeCommand(string $command): string
    {
        try {
            $command = array_values(json_decode($command, true, 512, JSON_THROW_ON_ERROR))[0] ?? die;
        } catch (JsonException) {
            throw new \RuntimeException('Command failed: ' . $command);
            die;
        }

        print "AI generated command: " . $command . PHP_EOL;

        print "Execute? (y/n): ";
        $execute = trim(fgets(STDIN));

        if ($execute !== 'y') {
            throw new \RuntimeException('Did not execute: ' . $command);
        }
        $output = shell_exec($command);

        if ($output === null) {
            throw new \RuntimeException('Command failed');
        }

        return $output;
    }

    private function createCommandQuery(): string
    {
        $term = $this->query;

        $job = sprintf('Create a terminal command for %s that returns a list of files (only the paths, no other data). If the user does not give a path to where the files are located, try a home folder. Do not (!!) give a response that includes an example such as /path/to/file. Use recursive search and if necessary, start from the home folder. The entire response must be in a json string array with one single command as such: ["COMMAND"]. The search is described by the following:', $this->systemConfig->get('os'));

        return sprintf('%s: %s', $job, $term);
    }

    private function createFileSelector(string $responseText): string
    {
        $fileContents = $this->readFileContents($responseText);

        $job = <<<TEXT
I have found the following files and their contents for you. Please choose which (at the most 3) files represent the search best by only returning the file name in full path.
TEXT;

        return sprintf('%s: %s', $job, $fileContents);
    }

    private function readFileContents(string $responseText): string
    {
        if (empty($responseText)) {
            return '';
        }

        $responseTextArray = explode(PHP_EOL, $responseText);

        $fileContents = '';

        $count = 0;

        foreach ($responseTextArray as $result) {
            if (!file_exists($result)) {
                continue;
            }

            $count++;

            $fileContents .= 'File "' . $result . '":' . PHP_EOL;
            $fileContents .= 'SOF>>' . PHP_EOL;
            $fileContents .= $this->grabFileContents($result);
            $fileContents .= '<<EOF' . PHP_EOL;
        }

        print "Suggesting $count files. to AI" . PHP_EOL;

        return $fileContents;
    }

    private function grabFileContents(string $file): string
    {
        $fileContents = file_get_contents($file);

        if ($fileContents === false) {
            return '';
        }

        $allLines = explode(PHP_EOL, $fileContents);

        $fileContents = '';

        foreach ($allLines as $line) {
            if (empty($line)) {
                continue;
            }

            if (str_contains($line, $this->query)) {
                $fileContents .= $line . PHP_EOL;
            }
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
