<?php

declare(strict_types=1);

namespace Kaliop\IbexaContentDto\Services\Factory;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use Kaliop\IbexaContentDto\Entity\DtoInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGeneratorInterface;

/**
 *
 */
final class IbexaDtoFactory
{
    private RouterInterface $router;
    private RouteReferenceGeneratorInterface $ezRouter;

    /**
     *
     */
    public function __construct()
    {
        global $kernel;
        $this->router = $kernel->getContainer()->get('router');
        $this->ezRouter = $kernel->getContainer()->get('ezpublish.route_reference.generator');
    }

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
                     $location,
        string       $currentLanguage
    ): DtoInterface
    {
        $self = new static;
        $router = $self->router;

        $dto
            ->setContent($content)
            ->setLocationId($content->contentInfo->mainLocationId)
            ->setIbexaUrl($router->generate('ez_urlalias', ['locationId' => $content->contentInfo->mainLocationId]))
        ;

        /** @todo : check multi positionnement */
        $name = $content->getName($currentLanguage);
        if (empty($name)) {
            $dto->setName('No translated name');
        } else {
            $dto->setName($name);
        }


        if (!is_null($location) && !empty($location->remoteId)) {
            $dto->setLocationRemoteId($location->remoteId);
        }

        if (!empty($content->contentInfo->remoteId)) {
            $dto->setContentRemoteId($content->contentInfo->remoteId);
        }

//        if (is_null($dto->isRestricted())) {
//            $dto->setIsRestricted(false);
//        }

        $normalizer = new CamelCaseToSnakeCaseNameConverter();
        $reflector = new \ReflectionClass($dto);

        foreach ($content->getFieldsByLanguage($currentLanguage) as $field) {
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
    private static function getValue(Content $content, Field $field)
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
                $selection = $field->value->selection[0];
                $value = $fieldSettings['options'][$selection];
                break;
            case 'ezobjectrelationlist':
                $value = $field->value->destinationContentIds;
                break;
            case 'ezdate':
                // @todo check value
                $value = $field->value->datetime;
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
        switch ($fieldTypeIdentifier) {
            case 'ezstring':
            case 'ezselection':
            case 'eztext':
                $type = 'string';
                break;
            case 'ezinteger':
                $type = 'int';
                break;
            case 'ezboolean':
                $type = 'bool';
                break;
            case 'ezrichtext':
                $type = '\DOMDocument';
                break;
            case 'ezobjectrelationlist':
                $type = 'array';
                break;
            case 'eztime':
            case 'ezdate':
                $type = '\DateTimeInterface';
                break;
            case 'ezimage':
                $type = '?ValueObject';
                break;
            case 'ezurl':
            default:
                $type = '';
        }

        return $type;
    }
}