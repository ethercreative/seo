![SEO for Craft CMS](resources/imgs/banner.jpg)

# SEO for Craft CMS

SEO for Craft does three things that will help your sites SEO, and does them really damn well *(and it has full localisation support!)*.

### The Field Type

The SEO field type helps give users an idea of how their page will look in Google, and how their pages content scores when compared to a specific keyword.

The field type allows users to manage the meta of their page in one simple and easy to use input that has the added bonus of giving them an idea of how their page will show up in a Google search.

The field also contains a *Focus Keyword* input and *Page Score*. This is used to workout how relevant a key word or phrase is to your entry and how well the page is likely to do in a search for that keyword.

The Page Score also contains a breakdown of your entries score, and tips on where it can be improved.

![SEO for Craft CMS](resources/imgs/fieldtype.png)

### The Sitemap

SEO for Craft boasts an extremely powerful, yet simple to use Sitemap manager. With automatic support for all your site’s sections and categories (with localisations taken into account), and the ability to easily add custom URLs (useful for public templates that aren’t content managed), keeping your sitemap up-to-date has never been easier.

With SEO for Craft’s sitemap manager you have complete control over what content you want to have appear on your sitemap as well as managing its change frequency and priority in your site.

### The Redirects

When moving from your old, awful site to your shiny new Craft one, you’ll want to make sure that all your old pages are redirected to their new counterparts. Redirects are easy to manage with SEO for Craft.

SEO for Crafts redirect manager lets you easily add 301 & 302 redirects, with full regex support!

## Installation & Usage

Clone this repo into `craft/plugins/seo`.

Before using the SEO field type, you’ll need to ensure all the settings are correct. You can find the settings under the SEO plugin menu in the sidebar, or via the plugin menu.

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

### Custom SEO Object

In some cases, you will not have access to an SEO field, but will want to set the page title & description. You can do this by creating a custom SEO object using the function below:

```twig
craft.seo.custom('The Page Title', 'The page description', $includeDefaultTitleSuffix)
```

The last parameter is a boolean that tell the plugin whether or not to include the title suffix after your title. It defaults to true.

All parameters are optional.


## TODO
- [ ] Add hooks for plugin support
- [ ] Include more fields in snippet (i.e. Type, Image, etc.)
- [ ] Allow for multiple focus keywords
- [ ] Make the SEO dashboard more useful. Somehow.

## Changelog

### 1.0.0
- Initial Release


---

Copyright © 2016 Ether Creative <hello@ethercreative.co.uk>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.