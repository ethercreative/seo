<?php

namespace Craft;

class Seo_SitemapService extends BaseApplicationComponent
{

	public function getValidSections ()
	{
		return array_filter(craft()->sections->allSections, function ($section) {
			return $section->urlFormat || $section->isHomepage();
		});
	}

}