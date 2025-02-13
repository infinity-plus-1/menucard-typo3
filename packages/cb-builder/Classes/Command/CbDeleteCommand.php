<?php

declare(strict_types=1);

namespace DS\CbBuilder\Command;

use DS\CbBuilder\BasicBuilder\BasicBuilder;
use DS\CbBuilder\Collector\Collector;
use DS\CbBuilder\Updater\Updater;
use DS\CbBuilder\Wrapper\Wrapper;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'cb:delete',
    description: 'Delete a content block.'
)]
class CbDeleteCommand extends Command
{

    const FILE_LIST = [
        ['dir' => '', 'file' => '/ext_localconf.php'],
        ['dir' => '/Configuration', 'file' => '/page.tsconfig'],
        ['dir' => '/Configuration/TypoScript', 'file' => '/setup.typoscript'],
        ['dir' => '/Configuration/TCA/Overrides', 'file' => '/tt_content.php']
    ];

    private static function _clearFromFile(string $path, string $dir, string $file, string $identifier): void
    {
        $path .= $dir;
        if (is_dir($path)) {
            $path .= $file;
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $boundary = Wrapper::getWrapBoundary($content, $identifier);
                $before = substr($content, 0, ($boundary['start']));
                $after = substr($content, $boundary['endNewline'], strlen(($content)));
                file_put_contents($path, $before . $after);
            }
        }
    }

    public static function _removeContentBlockFromList($identifier): void
    {
        $yamlPath = __DIR__ . "/../../ContentBlocks/contentBlocks.yaml";
        $list = Yaml::parseFile($yamlPath);
        if (isset($list['contentBlocks']) && isset($list['contentBlocks'][$identifier])) {
            unset($list['contentBlocks'][$identifier]);
            file_put_contents($yamlPath, Yaml::dump($list));
        }
    }

    public static function _deleteFolder(string $path, string $identifier): void
    {
        $path .= "/ContentBlocks/$identifier";
        if (is_dir($path)) {
            (new Filesystem())->remove($path);
        }
    }
    
    private static function _deleteContentBlock(array $contentBlock): void
    {
        $path = $contentBlock['path'] ?? NULL;
        $identifier = $contentBlock['identifier'] ?? NULL;
        if ($path == NULL || $identifier == NULL) {
            throw new Exception('');
        }
        foreach (CbDeleteCommand::FILE_LIST as $entry) {
            CbDeleteCommand::_clearFromFile($path, $entry['dir'], $entry['file'], $identifier);
        }
        CbDeleteCommand::_deleteFolder($path, $identifier);
        CbDeleteCommand::_removeContentBlockFromList($identifier);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $contentBlocks = Collector::collectContentBlocks();
        $question = new ChoiceQuestion(
            'Please select the content block to delete.',
            $contentBlocks,
            array_key_first($contentBlocks)
        );

        $contentBlocksCount = count($contentBlocks) - 1;

        $question->setErrorMessage("Please choose a content block in range 0-$contentBlocksCount");

        $contentBlock = $io->askQuestion($question);
        $contentBlock = $contentBlocks[$contentBlock];

        $question = new ChoiceQuestion(
            "Are you sure that you want to delete the content block $contentBlock?",
            ['no', 'yes'],
            0
        );
        $question->setErrorMessage("Please choose 'yes' or 'no'.");

        $submit = $io->askQuestion($question);

        if ($submit == 'no') return Command::SUCCESS;

        // $question = new ChoiceQuestion(
        //     "This process can't be reversed, do you really want to delete the content block?",
        //     ['no', 'yes'],
        //     0
        // );
        // $question->setErrorMessage("Please choose 'yes' or 'no'.");

        // $submit = $io->askQuestion($question);

        $contentBlocks = Collector::collectContentBlocks(false);
        
        $contentBlock = explode(' -> ', $contentBlock);
        $name = $contentBlock[0];
        $identifier = $contentBlock[1];
        $contentBlock = array_filter($contentBlocks, function ($element) use ($name, $identifier) {
            return $name === $element['name'] && $identifier === $element['identifier'];
        });

        if (is_array($contentBlock) && count($contentBlock) === 1) {
            CbDeleteCommand::_deleteContentBlock($contentBlock[$identifier]);
        } else {
            throw new Exception('No content block could be fetched. Please delete it manually. Read the FAQ for instructions.');
        }

        //Updater::updateFields("./packages/cb-builder", "new_content_block");
        return Command::SUCCESS;
    }
}