<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Iterators;

use Kaliop\IbexaContentDto\Entity\ContentDtoInterface;

/**
 *
 */
class DtoIterator implements \Iterator
{

    private int $position = 0;

    private DtoCollection $dtoCollection;

    /**
     * @param DtoCollection $dtoCollection
     */
    public function __construct(DtoCollection $dtoCollection)
    {
        $this->dtoCollection = $dtoCollection;
    }

    /**
     * @return ContentDtoInterface|null
     */
    public function current(): ?ContentDtoInterface
    {
        return $this->dtoCollection->getSubDto($this->position);
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return !is_null($this->dtoCollection->getSubDto($this->position));
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}