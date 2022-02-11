<?php

namespace Kaliop\IbexaContentDto\Services\String;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

final class CamelCaseStringify
{
    public function __invoke(string $value): string
    {
        $camelCaseConverter = new CamelCaseToSnakeCaseNameConverter();
        return ucfirst($camelCaseConverter->denormalize($value));
    }
}