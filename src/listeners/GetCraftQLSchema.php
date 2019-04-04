<?php

namespace ether\seo\listeners;

use markhuot\CraftQL\Events\GetFieldSchema;
use markhuot\CraftQL\Types\VolumeInterface;

class GetCraftQLSchema
{

	function handle (GetFieldSchema $event)
	{
		$event->handled = true;

		$socialObject =
			$event->schema->createObjectType('SeoDataSocial');
		$socialObject->addStringField('title');
		$socialObject->addField('image')->type(VolumeInterface::class);
		$socialObject->addStringField('description')->resolve(function ($root, $args) {
			return (string)$root->description;
		});

		$socialFieldObject =
			$event->schema->createObjectType('SeoDataSocialField');
		$socialFieldObject->addField('twitter')->type($socialObject);
		$socialFieldObject->addField('facebook')->type($socialObject);

		$fieldObject =
			$event->schema->createObjectType('SeoData');
		$fieldObject->addStringField('title')->resolve(function ($root, $args) {
			return (string)$root->getTitle()->__toString();
		});
		$fieldObject->addStringField('description')->resolve(function ($root, $args) {
			return (string)$root->getDescription()->__toString();
		});
		$fieldObject->addStringField('keywords')->resolve(function ($root, $args) {
			return $root->getKeywordsAsString();
		});
		//print_r($fieldObject->addStringField('keywords')); exit;
		//$fieldObject->addStringField('keywords');
		$fieldObject->addField('social')->type($socialFieldObject);

		$event->schema->addField($event->sender)->type($fieldObject);
	}

}