<?php

declare(strict_types=1);

namespace DS\CbBuilder\Command;

use DS\CbBuilder\BasicBuilder\BasicBuilder;
use DS\CbBuilder\Collector\Collector;
use DS\CbBuilder\Database\DatabaseUtility;
use DS\CbBuilder\FieldBuilder\FieldBuilder;
use DS\CbBuilder\Updater\Updater;
use DS\CbBuilder\Wrapper\Wrapper;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsCommand(
    name: 'cb:update',
    description: 'Update a content block.'
)]
class CbUpdateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fieldBuilder = new FieldBuilder('./packages/cb-builder', 'new_content_block');

        Updater::loadConfig();
        $fieldBuilder->buildFields();
        //$test = serialize($GLOBALS['TCA']['tt_content']['types']['new_content_block']);

        //dump(unserialize($GLOBALS['TCA']['tt_content']['types']['new_content_block']));
        return Command::SUCCESS;
    }

}