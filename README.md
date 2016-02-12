![SEO for Craft CMS](resources/imgs/banner.jpg)

# SEO for Craft CMS

Clone this repo into `craft/plugins/seo`.

### Fieldtype Usage

Replace or modify your current SEO head code with, or to match, the following:

```twig
{# SEO Start #}
{% if seo is not defined %}
    {% set seo = craft.seo.custom(siteName, '', false) %}
{% endif %}

<title>{{ seo.title }}</title>
<meta name="description" content="{{ seo.description }}" />

<meta property='og:title' content='{{ seo.title }}' />
<meta property='og:url' content='{{ craft.request.url }}' />
<meta property='og:site_name' content='{{ siteName }}' />
<meta property='og:description' content='{{ seo.description }}' />

<meta property='twitter:site' content='{# Your Twitter Handle (no @) #}' />
<meta property='twitter:title' content='{{ seo.title }}' />
<meta property='twitter:description' content='{{ seo.description }}' />
<meta property='twitter:url' content='{{ craft.request.url }}' />

<link rel="home" href="{{ siteUrl }}" />
<link rel="canonical" href="{{ craft.request.url }}">
{# SEO End #}
```

The code snippet above assumes that you will be creating a variable call `seo` in your templates that will return either the SEO field or a custom SEO object (see below).

**Custom SEO Object**

In some cases, you will not have access to an SEO field, but will want to set the page title & description. You can do this by creating a custom SEO object using the function below:

```twig
craft.seo.custom('The Page Title', 'The page description', $includeDefaultTitleSuffix)
```

The last parameter is a boolean that tell the plugin whether or not to include the title suffix after your title. It defaults to true.

All parameters are optional.


### TODO
* Sitemap support for categories
* Add hooks to sitemap for plugin support
* **Test with multiple locales**


---

Copyright © 2016 Ether Creative <hello@ethercreative.co.uk>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.