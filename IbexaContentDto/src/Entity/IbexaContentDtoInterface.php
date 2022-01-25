<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Entity;

/**
 *
 */
interface IbexaContentDtoInterface
{
    public function getName(): ?string;
    public function setName(string $name): ?IbexaContentDtoInterface;
    public function listObjectRelationListFields(): ?array;
}