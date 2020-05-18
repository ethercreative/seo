<?php

declare(strict_types=1);

namespace ether\seo\gql;

use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\ObjectType;

/**
 * Class SeoSocialNetworks
 *
 * @package ether\seo\gql
 */
class SeoSocialNetworks extends \craft\gql\base\ObjectType
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
     * @throws \craft\errors\GqlException
     */
    public static function getType(): ObjectType
    {
        if ($type = GqlEntityRegistry::getEntity(static::class)) {
            return $type;
        }

        return GqlEntityRegistry::createEntity(static::class, new \GraphQL\Type\Definition\ObjectType([
            'name' => static::getName(),
            'fields' => [
                'twitter' => SeoSocialData::getType(),
                'facebook' => SeoSocialData::getType()
            ]
        ]));
    }
}
