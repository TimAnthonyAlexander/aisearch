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

    private function executeCommand(string $command): ?string
    {
        try {
            $command = array_values(json_decode($command, true, 512, JSON_THROW_ON_ERROR))[0] ?? die;
        } catch (JsonException) {
            throw new \RuntimeException('Command failed: ' . $command);
            die;
        }

        $firstPart = explode(' ', $command)[0];
        $manPage = shell_exec('man ' . $firstPart);

        print "AI generated command: " . $command . PHP_EOL;
        print "You can decide upon these options: ".PHP_EOL;
        print "y: Execute command".PHP_EOL;
        print "n: Do not execute command".PHP_EOL;
        print "m: Read the manual page for the command".PHP_EOL;
        print "Action: ";
        $execute = trim(fgets(STDIN));

        if ($execute === 'y') {
            return shell_exec($command);
        }
        if ($execute === 'n') {
            throw new \RuntimeException('Command failed');
        }
        if ($execute === 'm') {
            print $manPage;
            print "You can decide upon these options: ".PHP_EOL;
            print "y: Execute command".PHP_EOL;
            print "n: Do not execute command".PHP_EOL;
            print "Action: ";
            $execute = trim(fgets(STDIN));

            if ($execute === 'y') {
                return shell_exec($command);
            }

            throw new \RuntimeException('Command failed');
        }

        throw new \RuntimeException('Command failed');
    }

    private function createCommandQuery(): string
    {
        $term = $this->query;

        $job = sprintf('Create a terminal command for %s that returns a list of files (only the paths, no other data). If the user does not give a path to where the files are located, try a home folder. Do not (!!) give a response that includes an example such as /path/to/file. Use recursive search and if necessary, start from the home folder. The entire response must be in a json string array with one single command as such: ["COMMAND"]. You will then receive the list of files generated, some of their content and their path, and you will be able to choose the files that match the rest of the user input. If there are dates given, use YYYY-MM-DD format. Do not use $(date) to generate dates, write them out. The current date is %s. The command might also search through the file using regex. The search is described by the following:', $this->systemConfig->get('os'), date('Y-m-d'));

        return sprintf('%s: %s', $job, $term);
    }

    private function createFileSelector(?string $responseText): string
    {
        if (empty($responseText)) {
            throw new \RuntimeException('No files found');
        }

        $fileContents = $this->readFileContents($responseText);

        $job = <<<TEXT
I have found the following files and these lines for you. Please choose which files represent the search best by only returning the file name in full path.
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

        foreach ($allLines as $number => $line) {
            if (empty($line)) {
                continue;
            }

            $fileContents .= $number . ': ' . $line . PHP_EOL;

            if ($number > 10) {
                break;
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
