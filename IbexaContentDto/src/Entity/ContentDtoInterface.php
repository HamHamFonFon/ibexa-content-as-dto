<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Entity;

/**
 *
 */
interface ContentDtoInterface
{
    public function getName(): ?string;
    public function setName(string $name): ?ContentDtoInterface;
    public function listObjectRelationListFields(): ?array;
}