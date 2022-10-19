<?php

return [
	"lorem_ipsum" => <<<EOT
	Integer posuere erat a ante venenatis dapibus posuere velit
	aliquet. Aenean lacinia bibendum nulla sed consectetur.
	Donec sed odio dui.
	EOT,
	"canonical_text_hint" => <<<EOT
	If this is not the canonical version of this page, add the
	canonical URL here. The current URL of the page will be used
	if this is left blank.
	EOT,
	"robots_text_hint" => <<<EOT
	Control how this page is indexed by crawlers (some of these
	are Google specific). If this element as an expiry date the
	<code>unavailable_after</code> directive will be set
	automatically.
	EOT,
	"noindex_help_text" => <<<EOT
	Do not show this page in search results and do not
	show a “Cached” link in search results.
	EOT,
	"nosnippet_help_text" => <<<EOT
	Do not show a text snippet or video preview in the
	search results for this page. A static thumbnail
	(if available) will still be visible.
	EOT,
	"noarchive_help_text" => "Do not show a “Cached” link in search results.",
	"pagetitle_settings_text" => "Create a token for each part of your title. Clicking the lock will prevent the user from editing the contents of that token. Tokens use the same syntax as [Dynamic Entry Titles](https://docs.craftcms.com/v3/sections-and-entries.html#dynamic-entry-titles). They can be dragged to re-order.",
	"pagedescription_settings_text" => "This supports the same syntax as [Dynamic Entry Titles](https://docs.craftcms.com/v3/sections-and-entries.html#dynamic-entry-titles).",
	"socialimage_settings_text" => "The image that will be used when the page is shared via social networks. This can be modified on a per-entry basis.",
	"socialimage_settings_error" => "You’ll need a volume with public URLs before you can select a social image!",
	"hidesocial_settings_text" => "Will hide the social meta tab when switched on. This is useful if you have pre-existing social media fields.",

	"redirect_instructions" => <<<EOT
	Redirects support regex.
	To redirect from {a} to {b} you would add the following redirect:
	{redirect}
	EOT,
	"metaInstructions" => "Decide how the page meta should look for pages that have an associated SEO field (i.e. static templates, anywhere `craft.seo.custom()` is used)",
	"metaTemplate_instructions" => "The template you want to use when rendering the SEO Meta. Leave blank to use the default template (<a href='https://github.com/ethercreative/seo/blob/v3/src/templates/_seo/meta.twig?ts=4' target='_blank'>which you can see here</a>). This will replace the <code>{% hook 'seo' %}</code> in your head tag.",
	"robots_instructions" => "Control how crawlers access your site. [Find out more](http://www.robotstxt.org/robotstxt.html). You have access to all Craft twig variables, globals, and `seo` which contains all the SEO settings.",
	"sitemap_instructions" => "The name of your sitemap file. It will be found at <strong>{siteUrl}<code id=\"sitemapNameExample\">{sitemapName}.xml</code></strong>",
	"pagination_limit_instructions" => "The max number of elements to limit each page to. Lower resource servers should reduce this number if sitemap pages are failing to generate.",
];
