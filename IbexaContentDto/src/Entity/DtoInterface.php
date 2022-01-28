<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Entity;

/**
 *
 */
interface DtoInterface
{
    public function getName(): ?string;
    public function setName(string $name): ?DtoInterface;
    public function listObjectRelationListFields(): ?array;
}