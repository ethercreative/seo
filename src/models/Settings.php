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

	/** @var boolean */
	public $removeAlternateUrls = false;

	// Variables: Field Type
	// -------------------------------------------------------------------------

	/**
	 * @var string
	 * @deprecated
	 */
	public $titleSuffix;

	/** @var array */
	public $title = [
		[
			'key'      => '1',
			'template' => '{title}',
			'locked'   => false,
		],
		[
			'key'      => '2',
			'template' => ' - {{ siteName }}',
			'locked'   => true,
		]
	];

	/** @var string */
	public $description;

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
Sitemap: {{ url(seo.sitemapName ~ '.xml') }}

{# Disallows #}
{% if craft.app.config.env != 'production' %}

{# Disallow access to everything when NOT in production #}
User-agent: *
Disallow: /

{% else %}

{# Disallow access to cpresources/ when live #}
User-agent: *
Disallow: /cpresources/

{% endif %}
xyzzy;

	// Variables: Social
	// -------------------------------------------------------------------------

	/** @var string */
	public $facebookAppId;

	/** @var string */
	public $twitterHandle;

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
            [
                ['removeAlternateUrls'],
                'boolean'
            ],
		];
	}

}