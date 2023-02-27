<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Factory;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use Kaliop\IbexaContentDto\Entity\DtoInterface;
use ReflectionClass;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Convert Content into DTO
 */
final class IbexaDtoFactory
{

    /**
     * @param DtoInterface $dto
     * @param Content $content
     * @param string $currentLanguage
     *
     * @return DtoInterface
     */
    public static function hydrateDto(
        DtoInterface $dto,
        Content $content,
        string $currentLanguage
    ): DtoInterface
    {
        $normalizer = new CamelCaseToSnakeCaseNameConverter();
        $reflector = new ReflectionClass($dto);

        // if mode than one translation, we force current language
        if(count($content->versionInfo->languageCodes) > 1) {
            $listFields = $content->getFieldsByLanguage($currentLanguage);
        } else {
            $listFields = $content->getFieldsByLanguage();
        }

        foreach ($listFields as $field) {
            $dtoPropertyName = $normalizer->denormalize($field->fieldDefIdentifier);
            if ($reflector->hasProperty($dtoPropertyName)) {
                $property = $reflector->getProperty($dtoPropertyName);
                $property->setAccessible(true);
                $property->setValue($dto, self::getValue($content, $field));
            }
        }

        return $dto;
    }

    /**
     * @param Content $content
     * @param Field $field
     * @return mixed
     */
    private static function getValue(Content $content, Field $field): mixed
    {
        return match ($field->fieldTypeIdentifier) {
            'ezstring', 'eztext' => $field->value->text,
            'ezinteger' => $field->value->value,
            'ezboolean' => $field->value->bool,
            'ezrichtext' => $field->value->xml,
            'ezobjectrelationlist' => $field->value->destinationContentIds,
            'ezselection' => static function() use($content, $field) {
                $fieldSettings = $content->getContentType()
                    ->getFieldDefinition($field->fieldDefIdentifier)
                    ->getFieldSettings();
                $selection = (0 < count($field->value->selection)) ? $field->value->selection[0] : null;
                return (!is_null($selection)) ? $fieldSettings['options'][$selection] : null;
            },
            'ezdate' => $field->value->date,
            'eztime' => $field->value->time,
            'ezimage' => (!is_null($field->value->id)) ? $field->value : null,
            default => $field->value
        };


    }

    /**
     * @param string $fieldTypeIdentifier
     *
     * @return string
     */
    public static function getType(string $fieldTypeIdentifier): string
    {
        return match ($fieldTypeIdentifier) {
            'ezstring', 'ezselection', 'eztext' => 'string',
            'ezinteger' => 'int',
            'ezboolean' => 'bool',
            'eztime', 'ezdate' => '\DateTimeInterface',
            'ezobjectrelationlist' => 'array',
            'ezrichtext' => '\DOMDocument',
            'ezimage' => '?ValueObject',
            default => ''
        };
    }


}