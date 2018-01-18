<?php

namespace ether\seo\migrations;

use craft\db\Migration;
use ether\seo\records\RedirectRecord;

class Install extends Migration
{

	public function safeUp ()
	{
		// Redirects
		// ---------------------------------------------------------------------

		$this->createTable(
			RedirectRecord::$tableName,
			[
				'id' => $this->primaryKey(),

				'uri'  => $this->string(255)->notNull(),
				'to'   => $this->string(255)->notNull(),
				'type' => $this->enum('type', ['301', '302'])->notNull(),

				'dateCreated' => $this->dateTime()->notNull(),
				'dateUpdated' => $this->dateTime()->notNull(),
				'uid'         => $this->uid()->notNull(),
			]
		);
	}

	public function safeDown ()
	{
		$this->dropTableIfExists(RedirectRecord::$tableName);
	}

}