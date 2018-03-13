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

	// Variables: Field Type
	// -------------------------------------------------------------------------

	/** @var string */
	public $titleSuffix;

	/** @var array */
	public $socialImage;

	/** @var string */
	public $metaTemplate;

	/** @var array */
	public $robots;

	// Variables: Robots
	// -------------------------------------------------------------------------

	/** @var string */
	public $robotsTxt = <<<xyzzy
{# Sitemap URL #}
Sitemap: {{ url(seo.sitemapName ~ ".xml") }}

{# Disallows #}
{% if craft.app.config.general.devMode %}

{# Disallow access to everything when in devMode #}
User-agent: *
Disallow: /

{% else %}

{# Disallow access to cpresources/ when live #}
User-agent: *
Disallow: /cpresources/

{% endif %}
xyzzy;

	// Methods
	// =========================================================================

	/**
	 * @return array
	 */
	public function rules (): array
	{
		return [
			[
				['sitemapName', 'sitemapLimit'],
				'required'
			],
			[
				['sitemapName', 'titleSuffix', 'metaTemplate'],
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