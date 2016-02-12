<?php

namespace Craft;

class SeoVariable
{

	public function custom($title = '', $description = '', $includeTitleSuffix = true)
	{
		return [
			'title' => $title ? $title . ($includeTitleSuffix ? ' ' . craft()->seo->settings()->titleSuffix : '') : '',
			'description' => $description ?: '',
		];
	}

}