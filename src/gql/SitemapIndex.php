<?php

namespace ether\seo\gql;

use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class SitemapIndex extends \craft\gql\base\ObjectType
{
	public static function getName(): string
	{
		return 'Ether_' . (new \ReflectionClass(static::class))->getShortName();
	}

	public static function getType(): Type
	{
		if ($type = GqlEntityRegistry::getEntity(static::class))
			return $type;

		return GqlEntityRegistry::createEntity(static::class, new ObjectType([
			'name' => static::getName(),
			'fields' => [
				'loc' => Type::string(),
				'lastmod' => Type::string(),
			],
		]));
	}
}
