<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Factory;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use Kaliop\IbexaContentDto\Entity\DtoInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Convert Content into DTO
 */
final class IbexaDtoFactory
{

    /**
     * @param DtoInterface $dto
     * @param $content
     * @param $location
     * @param string $currentLanguage
     *
     * @return DtoInterface
     */
    public static function hydrateDto(
        DtoInterface $dto,
                     $content,
        string       $currentLanguage
    ): DtoInterface
    {
        $normalizer = new CamelCaseToSnakeCaseNameConverter();
        $reflector = new \ReflectionClass($dto);

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
     * @return mixed
     */
    private static function getValue(Content $content, Field $field): mixed
    {
        switch ($field->fieldTypeIdentifier) {
            case 'ezstring':
            case 'eztext':
                $value = $field->value->text;
                break;
            case 'ezinteger':
                $value = $field->value->value;
                break;
            case 'ezboolean':
                $value = $field->value->bool;
                break;
            case 'ezrichtext':
                $value = $field->value->xml;
                break;
            case 'ezselection':
                $fieldSettings = $content->getContentType()
                    ->getFieldDefinition($field->fieldDefIdentifier)
                    ->getFieldSettings();
                $selection = (0 < count($field->value->selection)) ? $field->value->selection[0] : null;
                $value = (!is_null($selection)) ? $fieldSettings['options'][$selection] : null;
                break;
            case 'ezobjectrelationlist':
                $value = $field->value->destinationContentIds;
                break;
            case 'ezdate':
                $value = $field->value->date;
                break;
            case 'eztime':
                // @todo check value
                $value = $field->value->time;
                break;
            case 'ezimage':
                $value = (!is_null($field->value->id)) ? $field->value : null;
                break;
            case 'ezbinaryfile':
                //$value = $this->router->generate('ez_content_download')$field->value;
            case 'ezurl':
            default:
                return $field->value;
        }

        return $value;
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