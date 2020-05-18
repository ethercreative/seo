<?php

namespace ether\seo\migrations;

use Craft;
use craft\db\Migration;
use ether\seo\records\RedirectRecord;

/**
 * m200518_110721_add_order_to_redirects migration.
 */
class m200518_110721_add_order_to_redirects extends Migration
{
	/**
	 * @inheritdoc
	 */
	public function safeUp()
	{
		$this->addColumn(
			RedirectRecord::$tableName,
			'order',
			$this->integer()->null()
		);

		$rows = RedirectRecord::find()->select(['id', 'siteId'])->all();
		$siteCounts = [];

		foreach ($rows as $row)
		{
			if (!array_key_exists($row->siteId, $siteCounts))
				$siteCounts[$row->siteId] = 0;

			$record = RedirectRecord::findOne(['id' => $row->id]);
			$record->order = $siteCounts[$row->siteId];
			$record->save(false);

			$siteCounts[$row->siteId]++;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function safeDown()
	{
		$this->dropColumn(
			RedirectRecord::$tableName,
			'order'
		);
	}
}
