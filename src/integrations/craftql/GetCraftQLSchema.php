<?php

namespace ether\seo\integrations\craftql;

use markhuot\CraftQL\Events\GetFieldSchema;
use markhuot\CraftQL\Types\VolumeInterface;

class GetCraftQLSchema
{

	// Handlers
	// =========================================================================

	function handle (GetFieldSchema $event)
	{
		$event->handled = true;

		// Social Data
		$socialObject = $event->schema->createObjectType('SeoDataSocial');
		$socialObject->addStringField('title')->resolve(
			$this->_resolve('title')
		);
		$socialObject->addField('image')->type(VolumeInterface::class);
		$socialObject->addStringField('description')->resolve(
			$this->_resolve('description')
		);

		// Keyword
		$keywordObject = $event->schema->createObjectType('SeoKeyword');
		$keywordObject->addStringField('keyword');
		$keywordObject->addStringField('rating');

		// Social Fields
		$socialFieldObject = $event->schema->createObjectType('SeoDataSocialField');
		$socialFieldObject->addField('twitter')->type($socialObject);
		$socialFieldObject->addField('facebook')->type($socialObject);

		// SEO Data
		$fieldObject = $event->schema->createObjectType('SeoData');
		$fieldObject->addStringField('title')->resolve(
			$this->_resolve('title')
		);
		$fieldObject->addStringField('description')->resolve(
			$this->_resolve('description')
		);
		$fieldObject->addField('keywords')->type($keywordObject)->lists();
		$fieldObject->addField('social')->type($socialFieldObject);

		$event->schema->addField($event->sender)->type($fieldObject);
	}

	// Helpers
	// =========================================================================

	private function _resolve ($field)
	{
		return function ($root) use ($field) {
			return html_entity_decode((string) $root->$field);
		};
	}

}