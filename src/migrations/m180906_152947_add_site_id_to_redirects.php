<?php

namespace ether\seo\migrations;

use craft\db\Migration;
use ether\seo\records\RedirectRecord;

/**
 * m180906_152947_add_site_id_to_redirects migration.
 */
class m180906_152947_add_site_id_to_redirects extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
        	RedirectRecord::$tableName,
	        'siteId',
	        $this->integer()->null()
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(
        	RedirectRecord::$tableName,
	        'siteId'
        );
    }
}
