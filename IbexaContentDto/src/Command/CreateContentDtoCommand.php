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

    private QuestionHelper $questionHelper;

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
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->questionHelper = $this->get('question');
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Step 1 : list all content-type identifier
        $contentTypeIdentifierSelected = $input->getArgument('content_type') ?? $this->getContentType();
        if (is_null($contentTypeIdentifierSelected)) {
            return Command::fail;
        }

        // Step 2 : list fields of content type selected
        $contentType = $this->repository->getContentTypeService()->loadByIdentifier($contentTypeIdentifierSelected);

        // Step 3 : build DTO class

        // Step 4 : build Repository class

        return Command::SUCCESS;
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return string
     */
    private function getContentType(InputInterface $input, OutputInterface $output): string
    {
        $listContentTypes = $this->repository->getContentTypeService()->loadContentTypeList();
        $question = new ChoiceQuestion('Select a content-type', $listContentTypes);

        return $question->ask($input, $output, $question);
    }

    private function buildDtoClass(ContentType $contentType)
    {

    }
}