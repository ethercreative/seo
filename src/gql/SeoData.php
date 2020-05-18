<?php

declare(strict_types=1);

namespace ether\seo\gql;

use craft\gql\GqlEntityRegistry;
use craft\gql\interfaces\elements\Asset as AssetInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use craft\helpers\Gql;

/**
 * Class SeoData
 * @package ether\seo\gql
 */
class SeoData extends \craft\gql\base\ObjectType
{
    /**
     * @return string
     */
    public static function getName(): string
    {
        return "Ether_" . (new \ReflectionClass(static::class))->getShortName();
    }

    /**
     * @return Type
     */
    public static function getType(): Type
    {
        if ($type = GqlEntityRegistry::getEntity(static::class)) {
            return $type;
        }

        return GqlEntityRegistry::createEntity(static::class, new \GraphQL\Type\Definition\ObjectType([
            'name' => static::getName(),
            'fields' => [
                'title' => [
                    'type' => Type::string(),
                ],
                'description' => [
                    'type' => Type::string(),
                ],
                'keywords' => Type::listOf(new ObjectType([
                    'name' => 'Ether_SEOKeyword',
                    'fields' => ['keyword' => Type::string(), 'rating' => Type::string()]
                ])),
                'social' => SeoSocialNetworks::getType(),
                'advanced' => SeoAdvanced::getType(),
            ]
        ]));
    }
}
