<?php
/**
 * SEO
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\seo\migrations;

use craft\db\Migration;
use ether\seo\Seo;

/**
 * Class m190114_152300_upgrade_to_new_data_format
 *
 * @author  Ether Creative
 * @package ether\seo\migrations
 */
class m190114_152300_upgrade_to_new_data_format extends Migration
{

	public function safeUp ()
	{
		Seo::getInstance()->upgrade->toNewDataFormat();
	}

	public function safeDown ()
	{
		return false;
	}

}