<?php
namespace ether\seo\gql;

use craft\gql\GqlEntityRegistry;
use craft\gql\interfaces\elements\Asset as AssetInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use craft\helpers\Gql;

class SeoData extends \craft\gql\base\ObjectType
{
    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return self::class;
    }

    /**
     * @inheritdoc
     */
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
                    'name' => 'SEO Keyword',
                    'fields' => ['keyword' => Type::string(), 'rating' => Type::string()]
                ])),
                'social' => SeoSocialNetworks::getType()
            ]
        ]));
    }
}

class SeoSocialNetworks extends \craft\gql\base\ObjectType {
    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return self::class;
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


class SeoSocialData extends \craft\gql\base\ObjectType {
    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return self::class;
    }

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
                } else {
                    $awareOfAllPublicVolumes = true;
                }
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
