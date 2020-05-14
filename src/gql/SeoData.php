<?php
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
    public static function getName(): string
    {
        return "Ether_" . (new \ReflectionClass(static::class))->getShortName();
    }

    public static function getType(): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        return GqlEntityRegistry::createEntity(self::class, new \GraphQL\Type\Definition\ObjectType([
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

/**
 * Class SeoSocialNetworks
 * @package ether\seo\gql
 */
class SeoSocialNetworks extends \craft\gql\base\ObjectType
{
    public static function getName(): string
    {
        return "Ether_" . (new \ReflectionClass(static::class))->getShortName();
    }

    public static function getType(): ObjectType {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        return GqlEntityRegistry::createEntity(self::class, new \GraphQL\Type\Definition\ObjectType([
            'name' => static::getName(),
            'fields' => [
                'twitter' => SeoSocialData::getType(),
                'facebook' => SeoSocialData::getType()
            ]
        ]));
    }

}

/**
 * Class SeoSocialData
 * @package ether\seo\gql
 */
class SeoSocialData extends \craft\gql\base\ObjectType
{
    public static function getName(): string
    {
        return "Ether_" . (new \ReflectionClass(static::class))->getShortName();
    }

    /**
     * @return ObjectType
     * @throws \craft\errors\GqlException
     */
    public static function getType(): ObjectType {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        return GqlEntityRegistry::createEntity(self::class, new \GraphQL\Type\Definition\ObjectType([
            'name' => static::getName(),
            'description' => 'Social data for an individual Social network',
            'fields' => array_merge(self::getConditionalFields(), [
                'title' => [
                    'type' => Type::string(),
                ],
                'description' => [
                    'type' => Type::string(),
                ]
            ])
        ]));
    }

    /**
     * Get fields which may only be used depending on the craft Gql config
     * @throws \craft\errors\GqlException
     */
    protected static function getConditionalFields(): array
    {
        // Images may be in any public volume, so verify them all.
        $volumes = \Craft::$app->volumes->getPublicVolumes();
        $awareOfAllPublicVolumes = false;

        if (!empty($volumes)) {
            foreach ($volumes as $volume) {
                if (!Gql::isSchemaAwareOf('volumes.' . $volume->uid)) {
                    $awareOfAllPublicVolumes = false;
                    break;
                }

                $awareOfAllPublicVolumes = true;
            }
        }

        if ($awareOfAllPublicVolumes) {
            return [
                'image' => [
                    'name' => 'image',
                    'type' => AssetInterface::getType(),
                ]
            ];
        }
        return [];
    }
}

class SeoAdvanced extends \craft\gql\base\ObjectType
{
    public static function getName(): string
    {
        return "Ether_" . (new \ReflectionClass(static::class))->getShortName();
    }

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
