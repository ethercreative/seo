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
		$this->dropCheck();
		$this->alterColumn(SitemapRecord::$tableName, 'group', $this->enum('group', [
			'sections', 'categories', 'productTypes', 'customUrls',
		])->notNull());
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropCheck();
		$this->alterColumn(SitemapRecord::$tableName, $this->enum('group', [
			'sections', 'categories', 'customUrls',
		])->notNull());
	}

	/**
	 * Drop group check.
	 */
	protected function dropCheck()
	{
		if ($this->db->getIsMysql())
			return;

		$this->db->createCommand()->dropCheck('seo_sitemap_group_check', SitemapRecord::$tableName)->execute();
	}
}
