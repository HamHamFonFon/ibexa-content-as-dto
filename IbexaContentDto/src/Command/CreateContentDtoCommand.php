<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Command;

use Kaliop\IbexaContentDto\Services\Traits\IbexaServicesTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class CreateContentDtoCommand extends Command
{
    protected static $defaultName = 'kaliop:dto:create';

    use IbexaServicesTrait;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Create couple of DTO/Repository of a content type')
            ->addArgument('content_type', InputArgument::OPTIONAL, 'Identifier of content-type')
        ;
    }

    /**
     * @param $repository
     */
//    public function __construct($repository)
//    {
//        $this->repository = $repository;
//        parent::__construct();
//    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Step 1 : list all content-type identifier

        // Step 2 : list fields of content type selected

        // Step 3 : build DTO class

        // Step 4 : build Repository class

        return Command::SUCCESS;
    }


}