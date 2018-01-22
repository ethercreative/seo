<?php

namespace ether\seo\records;

use craft\db\ActiveRecord;

/**
 * Class SitemapRecord
 *
 * @property int    $id
 * @property string $group
 * @property string $url
 * @property string $frequency
 * @property float  $priority
 * @property bool   $enabled
 *
 * @package ether\seo\records
 */
class SitemapRecord extends ActiveRecord
{

	public static $tableName = '{{%seo_sitemap}}';

	public static function tableName ()
	{
		return self::$tableName;
	}

}