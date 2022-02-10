<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Command;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use Kaliop\IbexaContentDto\Services\Traits\IbexaServicesTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 *
 */
class CreateContentDtoCommand extends Command
{
    protected static $defaultName = 'kaliop:dto:create';

    private const SKELETON_DTO = 'IbexaContentDto/src/Resources/Files/SkeletonDto';
    private const SKELETON_REPO = 'IbexaContentDto/src/Resources/Files/SkeletonRepository';

    private Repository $repository;
    private string $kernelRootDir;
    private string $directoryRepository;
    private string $directoryDto;
    private array $contentTypeGroups;

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
     * @param string $kernelRootDir
     * @param string $directoryRepository
     * @param string $directoryDto
     * @param array $
     */
    public function __construct(Repository $repository, string $kernelRootDir, string $directoryRepository, string $directoryDto, array $contentTypeGroups)
    {
        $this->repository = $repository;
        $this->kernelRootDir = $kernelRootDir;
        $this->directoryRepository = $directoryRepository;
        $this->directoryDto = $directoryDto;
        $this->contentTypeGroups = $contentTypeGroups;
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
        $contentTypeIdentifierSelected = $input->getArgument('content_type') ?? $this->getContentType($input, $output);
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
        $authorizedContentTypesGroups = $this->contentTypeGroups ?? ['Content'];

        $listContentTypesGroups = array_filter($this->repository->getContentTypeService()->loadContentTypeGroups(),
            static function(ContentTypeGroup $contentTypeGroup) use ($authorizedContentTypesGroups) {
                return in_array($contentTypeGroup->identifier, $authorizedContentTypesGroups, true);
            }
        );

        $listContentTypes = array_merge(...array_map(function(ContentTypeGroup $contentTypeGroup) {
                $listContentType = $this->repository->getContentTypeService()->loadContentTypes($contentTypeGroup);
                return array_merge(...array_map(static function(ContentType $contentType) {
                    return [$contentType->identifier => $contentType->getName()];
                }, $listContentType));
            }, $listContentTypesGroups)
        );

        $questionHelper = $this->getHelper('question');
        $question = new ChoiceQuestion('Select a content-type', $listContentTypes);

        return $questionHelper->ask($input, $output, $question);
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