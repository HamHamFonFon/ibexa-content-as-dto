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

    private const SKELETON_DTO = 'IbexaContentDto/src/Resources/Files/SkeletonDto';
    private const SKELETON_REPO = 'IbexaContentDto/src/Resources/Files/SkeletonRepository';

    private Repository $repository;
    private QuestionHelper $questionHelper;
    private string $kernelRoot;

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
     * @param Repository $repository
     * @param string $kernelRoot
     */
    public function __construct(Repository $repository, string $kernelRoot)
    {
        $this->repository = $repository;
        $this->questionHelper = $this->get('question');
        $this->kernelRoot = $kernelRoot;
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

    /**
     * @param ContentType $contentType
     *
     * @return void
     */
    private function buildDtoClass(ContentType $contentType): bool
    {
        $mapping = [
            'namespace' => $nameSpace,
            'dtoClassName' => $fileName,
            'listFields' => $listFields,
            'getters' => '',
            'listObjectRelationList' => ''
        ];

        $fileContent = preg_replace_callback('#%(.*?)%#', static function($match) use ($mapping) {
            $findKey = $match[1];
            if (array_key_exists($findKey, $mapping)) {
                return $mapping[$findKey];
            }
        }, file_get_contents(sprintf('%s/%s', $this->kernelRoot, self::SKELETON_DTO)));

        $fullPath = sprintf('%s/%s/%s.php', $this->kernelRoot, $nameSpace, $fileName);
        if (!file_exists($fullPath)) {
            $this->createFile($fullPath, utf8_decode($fileContent));
        }

    }

    /**
     * @param string $fullPath
     * @param string $content
     *
     * @return void
     */
    private function createFile(string $fullPath, string $content): void
    {
        $handle = fopen($fullPath, 'wb+');

        fwrite($handle, $content);
        fclose($handle);
    }
}