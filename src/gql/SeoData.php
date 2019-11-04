<?php
namespace ether\seo\gql;

use craft\base\VolumeInterface;
use craft\gql\base\InterfaceType;
use craft\gql\GqlEntityRegistry;
use craft\gql\interfaces\elements\Asset as AssetInterface;
use craft\gql\types\elements\Asset;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class SeoData extends \craft\gql\base\ObjectType
{
    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'SeoData2';
    }

    /**
     * @inheritdoc
     */
    public static function getType(): self
    {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        $socialFieldObject = new ObjectType([
            'name' => 'SEO Social Data',
            'description' => 'Social data for an individual Social network',
            'fields' => [
                'title' => [
                    'type' => Type::string(),
                ],
                'image' => [
                    'type' => AssetInterface::getType(),
                ],
                'description' => [
                    'type' => Type::string(),
                ]
            ]
        ]);

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
                'social' => new ObjectType([
                    'name' => 'SEO social',
                    'twitter' => $socialFieldObject,
                    'facebook' => $socialFieldObject
                ]),
            ]
        ]));
    }
}
