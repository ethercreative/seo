<?php

namespace Craft;

class m171010_150200_seo_AddABUniqueIndex extends BaseMigration {

	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$this->createIndex(
			'seo_ab_data',
			'elementId,fieldId,locale',
			true
		);

		return true;
	}

}