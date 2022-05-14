<?php

declare(strict_types=1);

namespace ether\seo\gql;

use Craft;
use craft\gql\GqlEntityRegistry;
use craft\gql\interfaces\elements\Asset as AssetInterface;
use craft\helpers\Gql;
use craft\models\Volume;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Class SeoSocialData
 *
 * @package ether\seo\gql
 */
class SeoSocialData extends \craft\gql\base\ObjectType
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
            'description' => 'Social data for an individual Social network',
            'fields' => array_merge(static::getConditionalFields(), [
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
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected static function getConditionalFields(): array
    {
        // Images may be in any public volume, so verify them all.
        $volumes = array_filter(Craft::$app->volumes->getAllVolumes(), static fn (Volume $volume) => $volume->getFs()->hasUrls);
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
