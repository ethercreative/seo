<?php

namespace ether\seo\migrations;

use craft\db\Migration;
use ether\seo\records\RedirectRecord;
use ether\seo\records\SitemapRecord;
use ether\seo\Seo;

class Install extends Migration
{

	public function safeUp ()
	{
		// Sitemap
		// ---------------------------------------------------------------------

		$this->createTable(
			SitemapRecord::$tableName,
			[
				'id' => $this->primaryKey(),

				'group' => $this->enum('group', [
					'sections', 'categories', 'productTypes', 'customUrls',
				])->notNull(),
				'url' => $this->string(255)->notNull(),
				'frequency' => $this->enum('frequency', [
					'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly',
					'never',
				])->notNull(),
				'priority' => $this->float(1)->notNull(),
				'enabled' => $this->boolean()->notNull()->defaultValue(false),

				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid'         => $this->uid()->notNull(),
			]
		);

		// Redirects
		// ---------------------------------------------------------------------

		$this->createTable(
			RedirectRecord::$tableName,
			[
				'id' => $this->primaryKey(),

				'uri'    => $this->string(255)->notNull(),
				'to'     => $this->string(255)->notNull(),
				'type'   => $this->enum('type', ['301', '302'])->notNull(),
				'siteId' => $this->integer()->null(),
				'order'  => $this->integer()->null(),

				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid'         => $this->uid()->notNull(),
			]
		);
	}

	public function safeDown ()
	{
		$this->dropTableIfExists(SitemapRecord::$tableName);
		$this->dropTableIfExists(RedirectRecord::$tableName);
	}

}