# Changelog

### 2.1.2
- [Fixed] Fixed bug where redirects that had query parameters were being ignored.

### 2.1.1
- [Fixed] Fixed hide social switch always showing as off, even when it should be on

### 2.1.0
- [Fixed] Fixed first paragraph check returning both positive and negative #64 (via @jtenclay)
- [Fixed] Snippet description now has minimum height
- [Improved] It's now possible to pass social media meta to `craft.seo.custom`
- [Improved] Added option to hide social media tab 

### 2.0.0
- [Added] Added ability to specify titles, descriptions, and images for social media
- [Improved] SEO field now supports multiple keywords
- [Improved] Increased the max length for snippet descriptions #56
- [Improved] Optimization checklist is now always visible #51
- [Fixed] Sitemap should now only include entries that are enabled #52
- [Fixed] Redirects with parameters #47 #48 (via @DanielleDeman)
- [Fixed] Fix generating localized URLs for elements #45 #46 (via @ClemensSchneider)
- [Fixed] Fixed save requests #44 (via @roelvanhintum)

### 1.5.0
- [Fixed] `sitemap.xml` breaking if no elements can be found in a given criteria #43
- [Improved] You can now use `{% hook "seo" %}` to output all your SEO meta!

### 1.4.5
- Improved support of short phrases as the focus keyword
- Fixed a bug where non-url valid characters caused one of the keyword tests to fail
- Fixed JS bug when creating an SEO field #41

### 1.4.4
- More CSRF JS bug fixes #40 - @caleuanhopkins

### 1.4.3
- Fixed CSRF JS bug on the Sitemap #38/#39 - @caleuanhopkins

### 1.4.2
- Redirects now have a new and improved UI.
- You can now bulk import redirects!

### 1.4.1
- Fixed bug where products & categories weren't being split out correctly in the sitemaps
- Sitemap content sections now correctly show as disabled by default

### 1.4.0
- Sitemap is now split into appropriate sections and paginated. By default the pages are limited to 1000 elements, but this is configurable in the settings.
- Renamed General settings tab to Sitemap.

### 1.3.2
- Fixed JS bug on fields settings page
- Added ability to populate SEO fields by element type. Doing so will set all SEO fields to `{{ entry.title ~ ' ' ~ seoField.suffix }}`.

### 1.3.1
- Actual fix for #20

### 1.3.0
- Singles snippet title field now auto-populates.
- Made redirects regex support clearer (even I forgot).
- Field no longer throws "One SEO field only" error when using quick-edit modal thingies.
- Moved plugin files into `SEO/` directory, moved superfluous files out of plugin directory.
- Added fix for #26
- Added fix for #21
- Added @PetterRuud's fix for #20

### 1.2.3
- Added Craft Commerce product types to Sitemap.
- Sitemap and Redirects are now stored in their own database tables, fixing the issue with the ~194 limit.

### 1.1.3
- Fixed #15 - Fixed bug where global settings undefined on new install

### 1.1.2
- Fix #12 via @FrankZwiers - Fix for php error on adding a Quick Post widget
- Fixed #11 - Fixed bug causing an Uncaught TypeError when no paragraphs are on the page
- Fixed text parser reading script tags as text
- SEO Field now uses minified JS

### 1.1.1
- Added error notification
- Fix #10 via @bertoost - Fixed check empty sections and categories

### 1.1.0
- Fixed #5 - Fieldtype can now be used on any element type (but keyword & score will only be visible on entries).
- Fix #7 via @FrankZwiers - Check regex exec for null value
- Fix #8 via @roelvanhintum - Admin is unavailable from the console
- Removed all "Readability" settings! - We now examine the page exactly as Google would see it.
- **Added Craft Commerce Support**

### 1.0.5
- Fixed bug where redirects wouldn't be saved when there were more than ~246 rows ([#3](https://github.com/ethercreative/seo/issues/3)).
- Removed unused Public Path setting field.
- **NOTICE:** You may need to clear Crafts template cache in order for this update work!

### 1.0.4
- Fixed error on settings save when no readability fields were checked.

### 1.0.3
- Fixed bug where redirects containing trailing slashes were not redirected!

### 1.0.2
- Stopped SEO fields showing up in the Readability Check list for the SEO fieldtype.

### 1.0.1
- Fixed bug where the entry URL in the SEO fieldtype snippet was incorrect for Single entries.
- Fixed double slash on Homepage URL in SEO fieldtype snippet.

### 1.0.0
- Initial Release