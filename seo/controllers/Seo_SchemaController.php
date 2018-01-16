<?php

namespace Craft;

use Seo\db\SchemaDb;
use Seo\Models\Schema;

class Seo_SchemaController extends BaseController {

	/**
	 * @throws HttpException
	 */
	public function actionIndex ()
	{
		/** @var Schema $thing */
		$thing = craft()->seo_schema->getThing();
		$topLevelSchema = json_encode(
			array_merge([$thing], $thing->getChildren())
		);
		$thingProperties = json_encode($thing->getProperties());

		craft()->templates->includeCssResource('seo/css/schema.css');
		craft()->templates->includeJsFile(
			craft()->config->get('devMode')
				? 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js'
				: 'https://cdn.jsdelivr.net/npm/vue'
		);
		craft()->templates->includeJsResource('seo/js/SeoSchema.min.js');
		$initialVariables = <<<xyzzy
new SeoSchema($topLevelSchema, $thingProperties);
xyzzy;
		craft()->templates->includeJs($initialVariables, true);

		$this->renderTemplate('seo/schema');
	}

	/**
	 * @throws HttpException
	 */
	public function actionGetChildren ()
	{
		$schemaId = craft()->request->getRequiredPost('schemaId');
		$children = SchemaDb::getSubClassOf($schemaId);
		$this->returnJson(compact('children'));
	}

	/**
	 * @throws HttpException
	 */
	public function actionGetProperties ()
	{
		$schemaId = craft()->request->getRequiredPost('schemaId');
		$properties = SchemaDb::getPropertiesOf($schemaId);
		$this->returnJson(compact('properties'));
	}

}