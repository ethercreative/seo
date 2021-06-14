<?php
/**
 * SEO for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\seo\models\data;

use Craft;
use craft\elements\Asset;
use ether\seo\models\Settings;
use ether\seo\Seo;
use yii\base\BaseObject;

function get_public_properties ($class) {
	return get_object_vars($class);
}

/**
 * Class SocialData
 *
 * @author  Ether Creative
 * @package ether\seo\models\data
 */
class SocialData extends BaseObject
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/** @var string */
	public $handle = '';

	/** @var string */
	public $title = '';

	/** @var string|int */
	public $imageId = null;

	/** @var string */
	public $description = '';

	// Properties: Private
	// -------------------------------------------------------------------------

	/** @var array */
	private $_fallback;

	/** @var string */
	private $_network;

	// Constructor
	// =========================================================================

	public function __construct (string $network, array $fallback = null, array $config = [])
	{
		$this->_network = $network;
		$this->_fallback = $fallback;

		if (array_key_exists('image', $config))
		{
			$config['imageId'] = $config['image'];
			unset($config['image']);
		}

		parent::__construct($config);
	}

	// Init
	// =========================================================================

	public function init ()
	{
		/** @var Settings $seoSettings */
		$seoSettings = Seo::$i->getSettings();

		// Fallbacks
		foreach (get_public_properties($this) as $key => $value)
			if (empty($value) && array_key_exists($key, $this->_fallback))
				$this->$key = $this->_fallback[$key];

		// Network Specific
		switch ($this->_network)
		{
			case 'facebook':
				$this->handle = $seoSettings->facebookAppId;
				break;
			case 'twitter':
				$this->handle = $seoSettings->twitterHandle;
				break;
		}
	}

	// Getters
	// =========================================================================

	/**
	 * @return array|Asset|null
	 */
	public function getImage ()
	{
		$image = $this->imageId;

		if (is_array($image) && isset($image[0]))
			$image = $image[0]['id'];

		if (empty($image))
			$image = $this->_fallback['image'];

		if (!($image instanceof Asset))
			$image = Craft::$app->getAssets()->getAssetById((int) $image);

		return $image;
	}

}
