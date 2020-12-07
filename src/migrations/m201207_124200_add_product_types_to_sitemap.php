<?php

namespace ether\seo\migrations;

use Craft;
use craft\db\Migration;
use ether\seo\records\SitemapRecord;

/**
 * m201207_124200_add_product_types_to_sitemap migration.
 */
class m201207_124200_add_product_types_to_sitemap extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->alterColumn(SitemapRecord::$tableName, 'group', [
			'values' => ['sections', 'categories', 'productTypes', 'customUrls'],
			'column' => 'enum',
			'required' => true,
		]);
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->alterColumn(SitemapRecord::$tableName, 'group', [
			'values' => ['sections', 'categories', 'customUrls'],
			'column' => 'enum',
			'required' => true,
		]);
	}
}
