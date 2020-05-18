<?php

declare(strict_types=1);

namespace ether\seo\gql;

use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class SeoAdvanced
 *
 * @package ether\seo\gql
 */
class SeoAdvanced extends \craft\gql\base\ObjectType
{
    /**
     * @return string
     */
    public static function getName(): string
    {
        return "Ether_" . (new \ReflectionClass(static::class))->getShortName();
    }

    /**
     * @return ObjectType
     */
    public static function getType(): ObjectType
    {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        return GqlEntityRegistry::createEntity(self::class, new \GraphQL\Type\Definition\ObjectType([
            'name' => static::getName(),
            'description' => 'Robots and canonical data',
            'fields' => [
                'robots' => Type::listOf(Type::string()),
                'canonical' => Type::string()
            ]
        ]));
    }
}
