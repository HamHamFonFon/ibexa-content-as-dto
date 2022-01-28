<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Iterators;

use Kaliop\IbexaContentDto\Entity\DtoInterface;

/**
 *
 */
class DtoCollection implements \IteratorAggregate
{

    private array $subDto = [];

    /**
     * @return DtoIterator
     */
    public function getIterator(): DtoIterator
    {
        return new DtoIterator($this);
    }

    /**
     * @param DtoInterface $dto
     *
     * @return void
     */
    public function addSubDto(DtoInterface $dto): void
    {
        $this->subDto[] = $dto;
    }

    /**
     * @param int $position
     *
     * @return void
     */
    public function getSubDto(int $position): ?DtoInterface
    {
        return $this->subDto[$position] ?? null;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->subDto) ?? 0;
    }
}