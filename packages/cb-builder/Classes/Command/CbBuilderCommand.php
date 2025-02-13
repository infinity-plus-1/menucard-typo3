<?php

declare(strict_types=1);

namespace DS\CbBuilder\Command;

use DS\CbBuilder\BasicBuilder\BasicBuilder;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'cb:make',
    description: 'Kickstart a new content block.'
)]
class CbBuilderCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('This command does nothing. It always succeeds.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $identifier = $io->ask('Enter an identifier for the new content block.', 'new_content_block', function ($identifier) {
            $matches = [];
            preg_match("/[\w]{2,}/", $identifier, $matches);
            if (!isset($matches[0]) || $matches[0] !== $identifier) {
                throw new Exception (
                    "Wrong identifier declaration. Allowed characters are " .
                    "alphanumeric and '_' with a minimum length of 2."
                );
            }
            $contentBlocks = Yaml::parseFile(__DIR__ . '/../../ContentBlocks/contentBlocks.yaml');
            if (is_array($contentBlocks['contentBlocks']) && array_key_exists($identifier, $contentBlocks['contentBlocks'])) {
                throw new Exception (
                    "A content block with that identifier exists already. Please choose an another identifier."
                );
            }
            return $identifier;
        });

        $splittedIdentifier = preg_split("/[_]{1}/", $identifier);

        $name = '';

        foreach ($splittedIdentifier as $value) {
            $name .= strtoupper($value[0]) . substr($value, 1) . ' ';
        }

        $name = trim($name);

        $name = $io->ask('Enter the name for the new content block.', $name, function ($name) {
            $matches = [];
            preg_match("/[\w ]{2,}/", $name, $matches);
            if (!isset($matches[0]) || $matches[0] !== $name) {
                throw new Exception (
                    "Wrong name declaration. Allowed characters are " .
                    "alphanumeric, '_' and ' ' with a minimum length of 2."
                );
            }
            return $name;
        });

        $namespace = $io->ask('Enter the vendor directory path of your extensions.', 'packages', function ($namespace) {
            $matches = [];
            preg_match("/[a-zA-Z_][\w]+(\/)*([a-zA-Z_][\w\/]+)*/", $namespace, $matches);
            if (!isset($matches[0]) || $matches[0] !== $namespace) {
                throw new Exception (
                    "Wrong namespace declaration. First declare the vendor name starting with letters or an underscore and followed" .
                    " by a blackslash (\\). Then define the path with the same requirements."
                );
            }
            return $namespace;
        });
        
        
        $dir = dir('./' . $namespace);
        $extensions = [];
        if ($dir) {
            while (false !== ($obj = $dir->read())) {
                if ($obj !== '.' && $obj !== '..') {
                    $extensions[] = $obj;
                }
            }
        }

        $extensionCount = count($extensions);

        if ($extensionCount <= 0) {
            throw new Exception('No extensions are available. Please kickstart an extension first.');
        }

        $question = new ChoiceQuestion(
            'Please choose the extension to add the content block to.',
            $extensions,
            $extensions[0]
        );
        
        $extensionCount--;
        $question->setErrorMessage("Please choose an extension in range 0-$extensionCount");
        
        $extension = $io->askQuestion($question);


        $path = $dir->path . '/' . $extension;

        if (is_dir($path . "/ContentBlocks/$identifier")) {
            throw new Exception('A folder with that identifier exists in the extension already.');
        }

        $desc = $io->ask('Please describe your content block.', "This is the content block $name");

        $placeAt = $io->ask('Choose an existing CType where to place the new content block.', 'textmedia');

        $question = new ChoiceQuestion(
            'Do you want to place the new content block \'before\' or \'after\' the existing CType?',
            ['after', 'before'],
            0
        );
        $question->setErrorMessage("Please choose between 'before' or 'after'");
        
        $position = $io->askQuestion($question);

        $group = $io->ask('Which group shall the CType get added to?.', 'default');

        $question = new ChoiceQuestion(
            'Do you wish to include a header and textarea to your content block by default?',
            ['yes', 'no'],
            0
        );
        $question->setErrorMessage("Please choose between 'yes' or 'no'");
        
        $include = $io->askQuestion($question);

        BasicBuilder::makeContentBlocksDir($path);
        BasicBuilder::buildBasicStructure($path, $extension, $namespace, $name, $identifier, $desc, $placeAt, $position, $group, $include);

        return Command::SUCCESS;
    }
}

?>