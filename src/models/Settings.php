<?php

namespace ether\seo\models;

use craft\base\Model;

class Settings extends Model
{

	// Variables
	// =========================================================================

	// Variables: Sitemap
	// -------------------------------------------------------------------------

	/** @var string */
	public $sitemapName = 'sitemap';

	/** @var int */
	public $sitemapLimit = 1000;

	// Variables: Redirects
	// -------------------------------------------------------------------------

	/** @var string */
	public $publicPath;

	// Variables: Field Type
	// -------------------------------------------------------------------------

	/** @var string */
	public $titleSuffix;

	/** @var array */
	public $socialImage;

	/** @var string */
	public $metaTemplate;

	// Methods
	// =========================================================================

	/**
	 * @return array
	 */
	public function rules (): array
	{
		return [
			[
				['sitemapName', 'sitemapLimit', 'publicPath'],
				'required'
			],
			[
				['sitemapName', 'publicPath', 'titleSuffix', 'metaTemplate'],
				'string'
			],
			[
				['sitemapLimit'],
				'number'
			],
			[
				['socialImage'],
				'each', 'rule' => ['integer']
			],
		];
	}

}