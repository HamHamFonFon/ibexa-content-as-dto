<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\String;

use Exception;
use Nadar\PhpComposerReader\AutoloadSection;
use Nadar\PhpComposerReader\ComposerReader;

/**
 *
 */
final class NamespaceCreator
{
    /** @var string */
    private string $kernelRootDir;

    /**
     * @return mixed
     */
    public function getKernelRootDir(): string
    {
        return $this->kernelRootDir;
    }

    /**
     * @required
     * @param mixed $kernelRootDir
     *
     * @return NamespaceCreator
     */
    public function setKernelRootDir(string $kernelRootDir): NamespaceCreator
    {
        $this->kernelRootDir = $kernelRootDir;
        return $this;
    }


    /**
     * @param string $fullPath
     *
     * @return void
     * @throws Exception
     */
    public function buildNamespace(string $fullPath): ?string
    {
        $namespace = null;

        // Read composer JSON
        $reader = new ComposerReader(sprintf('%s/composer.json', $this->getKernelRootDir()));
        if (!$reader->canRead()) {
            throw new \RuntimeException("composer.json file is not readable");
        }

        // get type of autoload (psr-0 or psr-4)
        $section = new AutoloadSection($reader, AutoloadSection::TYPE_PSR0);
        if (0 === $section->count()) {
            $section = new AutoloadSection($reader, AutoloadSection::TYPE_PSR4);
        }

        while ($section->valid()) {
            $currentSection = $section->current();
            if (true === str_contains($currentSection->source, $fullPath)) {
                $sourceNamespace = $currentSection->source;
                $prefixNamespace = $currentSection->namespace;
                break;
            }
            $section->next();
        }

        return $prefixNamespace . str_replace(DIRECTORY_SEPARATOR, '\\', substr($fullPath, strlen($sourceNamespace)));
    }
}