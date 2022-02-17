<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Command;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use Kaliop\IbexaContentDto\Services\Factory\IbexaDtoFactory;
use Kaliop\IbexaContentDto\Services\String\CamelCaseStringify;
use Kaliop\IbexaContentDto\Services\String\NamespaceCreator;
use Kaliop\IbexaContentDto\Services\Traits\IbexaServicesTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
    private NamespaceCreator $namespaceCreator;
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
     * @param NamespaceCreator $namespaceCreator
     * @param string $kernelRootDir
     * @param string $directoryRepository
     * @param string $directoryDto
     * @param array $contentTypeGroups
     */
    public function __construct(
        Repository $repository,
        NamespaceCreator $namespaceCreator,
        string $kernelRootDir,
        string $directoryRepository,
        string $directoryDto,
        array $contentTypeGroups
    )
    {
        $this->repository = $repository;
        $this->namespaceCreator = $namespaceCreator;
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
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Step 1 : list all content-type identifier
        $contentTypeIdentifierSelected = $input->getArgument('content_type') ?? $this->getContentType($input, $output);
        if (is_null($contentTypeIdentifierSelected)) {
            return Command::FAILURE;
        }

        // Step 2 : list fields of content type selected
        $output->writeln('Content-type selected : ' . $contentTypeIdentifierSelected);
        // todo : check content-type
        $contentType = $this->repository->getContentTypeService()->loadContentTypeByIdentifier($contentTypeIdentifierSelected);

        $question = $this->getHelper('question');

        // Step 3 : create DTO class
//        $questionDto = new ChoiceQuestion('Do you want generate DTO class ?', ['no', 'yes']);
//        $isBuildDto = $question->ask($input, $output, $questionDto);
//        if (1 === $isBuildDto) {
            [$dtoNameSpace, $dtoClassName] = $buildTo = $this->buildDtoClass($contentType, $input, $output);
            if (is_null($buildTo)) {
                return 0;
            }
            $output->writeln(sprintf('DTO class "%s\%s" have been created', $dtoNameSpace, $dtoClassName));
//        }


        // Step 4 : Create repository class
//        $questionRepo = new ChoiceQuestion('Do you want generate Repository class ?', ['no', 'yes']);
//        $isBuildRepo = $question->ask($input, $output, $questionRepo);
//        if (1 === $isBuildRepo) {
            [$repoNamespace, $repoClassname] = $buildRepository = $this->buildRepository($dtoNameSpace, $dtoClassName, $contentType->identifier, $input, $output);
            if (is_null($buildRepository)) {
                return Command::FAILURE;
            }

            $output->writeln(sprintf('Repository class "%s\%s" have been created', $repoNamespace, $repoClassname));
//        }

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
        $question = new ChoiceQuestion('Select a content-type :', $listContentTypes);

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param ContentType $contentType
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws \Exception
     */
    private function buildDtoClass(ContentType $contentType, InputInterface $input, OutputInterface $output): ?array
    {
        $camelCaseStringify = new CamelCaseStringify;

        // Get class name
        $className = $camelCaseStringify($contentType->identifier);

        // Get file name and path
        $fileName = sprintf('%s.php', $className);

        $filePath = sprintf('%s/%s', $this->directoryDto, $fileName);
        $fullFilePath = sprintf('%s/../%s/%s', $this->kernelRootDir, $this->directoryDto, $fileName);

        // Get namespace
        $nameSpace = $this->namespaceCreator->buildNamespace($filePath);

        if (class_exists($nameSpace . '\\' . $className)) {
            $questionHelper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                sprintf('Class "%s" already exist, do you want to continue or delete and recreate it or abort ?', $className),
                [
                    'continue', 'delete', 'abort'
                ],
                0
            );

            $response = $questionHelper->ask($input, $output, $question);
            if (('delete' === $response) && file_exists($fullFilePath)) {
                unlink($fullFilePath);
            }

            if ('abort' === $response) {
                return null;
            }
        }

        $output->writeln(sprintf('Generate "%s" classname in progress', $className));

        // List fields and getters
        $listFields = $contentType->getFieldDefinitions();
        $listProperties = $listGetters = '';

        foreach ($listFields as $field) {
            $attribute = $camelCaseStringify($field->identifier);
            $type = IbexaDtoFactory::getType($field->fieldTypeIdentifier);

            $listProperties .= sprintf('%sprotected %s $%s;%s',"\t", $type, lcfirst($attribute), PHP_EOL);

            $listGetters .= sprintf(
                '%spublic function get%s(): ?%s%s%s{%s%sreturn $this->%s;%s%s}%s',
                "\t",
                $attribute,
                $type,
                PHP_EOL,
                "\t",
                PHP_EOL,
                "\t\t",
                lcfirst($attribute),
                PHP_EOL,
                "\t",
                PHP_EOL . PHP_EOL
            );
        }

        $getListRelationFields = array_filter(array_map(static function(FieldDefinition $field) use($camelCaseStringify) {
            return ("ezobjectrelationlist" === $field->fieldTypeIdentifier) ? sprintf('\'%s\'', $camelCaseStringify($field->identifier)) : null;
        }, $listFields));

        $mapping = [
            'namespace' => $nameSpace,
            'dtoClassName' => $className,
            'listProperties' => $listProperties,
            'getters' => $listGetters,
            'listObjectRelationList' => (0 < count($getListRelationFields)) ? sprintf('[%s]', implode(',', $getListRelationFields)) : 'null'
        ];

        $fileContent = $this->createContent(self::SKELETON_DTO, $mapping);

        if (!file_exists($fullFilePath)) {
            $this->createFile($fullFilePath, utf8_decode($fileContent));
        }

        return [$nameSpace, $className];
    }

    /**
     * @param string $dtoNamespace
     * @param string $dtoClassname
     * @param string $contentType
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return array|null
     * @throws \Exception
     */
    private function buildRepository(string $dtoNamespace, string $dtoClassname, string $contentType, InputInterface $input, OutputInterface $output): ?array
    {
        $className = sprintf('%sRepository', $dtoClassname);
        $fileName = sprintf('%s.php', $className);

        $filePath = sprintf('%s/%s', $this->directoryRepository, $fileName);
        $fullFilePath = sprintf('%s/../%s/%s', $this->kernelRootDir, $this->directoryRepository, $fileName);

        $nameSpace = $this->namespaceCreator->buildNamespace($filePath);

        if (class_exists($nameSpace . '\\' . $className)) {
            $questionHelper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                sprintf('Class "%s" already exist, do you want to continue or delete and recreate it or abort ?', $className),
                [
                    'continue', 'delete', 'abort'
                ],
                0
            );

            $response = $questionHelper->ask($input, $output, $question);
            if (('delete' === $response) && file_exists($fullFilePath)) {
                unlink($fullFilePath);
            }

            if ('abort' === $response) {
                return null;
            }
        }

        $mapping = [
            'namespace' => $nameSpace,
            'dtoNamespaceClassName' => sprintf('%s\%s', $dtoNamespace, $dtoClassname),
            'dtoRepository' => $className,
            'dtoIdentifier' => $contentType,
            'dtoClassName' => $dtoClassname
        ];

        $fileContent = $this->createContent(self::SKELETON_REPO, $mapping);

        if (!file_exists($fullFilePath)) {
            $this->createFile($fullFilePath, utf8_decode($fileContent));
        }

        return [$nameSpace, $className];
    }

    /**
     * @param string $skeletonFile
     * @param array $mapping
     *
     * @return array|string|string[]|null
     */
    private function createContent(string $skeletonFile, array $mapping)
    {
        return preg_replace_callback('#%(.*?)%#', static function($match) use ($mapping) {
            $findKey = $match[1];
            if (array_key_exists($findKey, $mapping)) {
                return $mapping[$findKey];
            }
        }, file_get_contents(sprintf('%s/../%s', $this->kernelRootDir, $skeletonFile)));
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
