<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\String;

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
     */
    public function buildNamespace(string $fullPath): ?string
    {
        $namespace = null;

        // Read composer JSON
        $reader = new ComposerReader(sprintf('%s/composer.json', $this->getKernelRootDir()));

        // get type of autoload (psr-0 or psr-4)
        $section = new AutoloadSection($reader, AutoloadSection::TYPE_PSR4);




        return $namespace;
    }
}