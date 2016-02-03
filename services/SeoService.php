<?php

namespace Craft;

class SeoService extends BaseApplicationComponent
{

	public function settings ()
	{
		return craft()->plugins->getPlugin('seo')->getSettings();
	}

}