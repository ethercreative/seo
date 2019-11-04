<?php
namespace ether\seo\gql;

use craft\base\VolumeInterface;
use craft\gql\base\InterfaceType;
use craft\gql\interfaces\elements\Asset as AssetInterface;
use craft\gql\types\elements\Asset;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class SeoDataType extends InterfaceType {

    /**
     * Returns the schema object name
     *
     * @return string
     */
    public static function getName(): string
    {
        return 'SeoData';
    }

    /**
     * Returns the associated type generator class.
     *
     * @return string
     */
    public static function getTypeGenerator(): string
    {
        return SeoData::class;
    }
}

class SeoData extends ObjectType
{
    public function __construct()
    {
        $socialFieldObject = new ObjectType([
            'name' => 'SEO Social Data',
            'description' => 'Social data for an individual Social network',
            'fields' => [
                'title' => [
                    'type' => Type::string(),
                    'resolve' => static function($value) { return html_entity_decode($value); }
                ],
                'image' => [
                    'type' => AssetInterface::getType(),
                ],
                'description' => [
                    'type' => Type::string(),
                    'resolve' => static function($value) { return html_entity_decode($value); }
                ]
            ]
        ]);

        $config = [
            // Note: 'name' is not needed in this form:
            // it will be inferred from class name by omitting namespace and dropping "Type" suffix
            'fields' => [
                'title' => [
                    'type' => Type::string(),
                    'resolve' => static function($value) { return html_entity_decode($value); }
                ],
                'description' => [
                    'type' => Type::string(),
                    'resolve' => static function($value) { return html_entity_decode($value); }
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
        ];
        parent::__construct($config);
    }
}
