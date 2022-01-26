<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Iterators;

use Kaliop\IbexaContentDto\Entity\ContentDtoInterface;

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
     * @param ContentDtoInterface $dto
     *
     * @return void
     */
    public function addSubDto(ContentDtoInterface $dto): void
    {
        $this->subDto[] = $dto;
    }

    /**
     * @param int $position
     *
     * @return void
     */
    public function getSubDto(int $position): ?ContentDtoInterface
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