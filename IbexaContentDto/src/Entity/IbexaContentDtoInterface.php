<?php

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