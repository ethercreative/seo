## 4.2.2 - 2024-02-16
### Fixed
- use pagination information on canonical (via @therealpecus)
- sitemap element type grouping (via @jamesedmonston)

## 4.2.1 - 2023-08-23
### Fixed
- Remove trailing `?` if token removed from URL (Fixes #457 part 2)
- Restore whitespace in SEO tokens (Fixes #452)

### Changed
- Sanitize URL before outputting in meta (Fixes #454)

## 4.2.0 - 2023-08-18
### Fixed
- Fixed absolute URL including token query parameter (Fixes #457)
- Fixed error on Shopify plugin products (#456 via @ttempleton)

## 4.1.2 - 2023-05-15
### Fixed
- Fix robots always injecting no-index (Fixes #432)

## 4.1.1 - 2023-04-19
### Fixed
- Fix redirect page 500 error (Fixes #448)
- Remove HTML from fail toast (Fixes #449)
- Fix redirect deletion (Fixes #441)

## 4.1.0 - 2023-04-12
### Fixed
- Fix SEO field focus keywords on Craft 4 (Fixes #431, #407, via @dyerc)
- Deleted sites are no longer included in the redirect select (via @thomascoppein)
- Fix env check (Fixes #432, via @jacotijssen & @thomascoppein)
- Fix custom fields not being evaluated in dynamic syntax (Fixes #415, via @juban)

## 4.0.3 - 2022-06-08
### Fixed
- Fix sitemap render issue (Fixes #409, via @niektenhoopen)

## 4.0.2 - 2022-05-18
### Fixed
- Fix permission settings (Fixes #399, #401, via @niektenhoopen)

## 4.0.1 - 2022-05-10
### Fixed
- Fix GraphQL support

## 4.0.0 - 2022-05-04
### Fixed
- Support Craft v4 (via @niektenhoopen)

## 3.7.4 - 2021-06-14
### Fixed
- Fix error when trying to fetch social image (Fixes #358, via @MDXDave)

## 3.7.3 - 2021-05-28
### Changes
- Up Craft CMS requirement to `^3.5` (Fixes #341)

### Fixed
- Fix JS error on sitemap admin (Fixes #325)

## 3.7.2 - 2021-05-25
### Changed
- SEO will now add `noindex` to all environments except production, regardless of `devMode` (via @nstCactus)

### Fixed
- Fix sitemap error if some sites are disabled
- Fix an exception when an entry type has multiple SEO fields (via @nstCactus)
- Fix custom SEO objects always using fallback image (via @jmauzyk)

## 3.7.1 - 2021-04-22
### Fixed
- Fix migration issue when upgrading to 3.7 on MySQL

## 3.7.0 - 2021-04-22
### Added
- Added support for product types in sitemap (via @boboldehampsink)

## 3.6.7 - 2020-11-30
### Changed
- Set Twitter image transform to a 2:1 ratio (via @icreatestuff)
- Hide the "Settings" links when admin changes are disallowed (via @nstCactus)

### Fixed
- Fix getSettingsHtml function to be compatible with Craft 3.5 (via @bendesilva)
- Fix error in retrieving preview if no SVG tags are present on the page (via @tschoffelen)
- Fix ErrorException error on sections XML (via @jesuismaxime)

## 3.6.6 - 2020-06-30
### Added
- Add gql field-type definition for Craft 3.3 GraphQL implementation (via @FreekVR)

## 3.6.5.2 - 2020-6-24
### Fixed
- Fix incorrect content in fields on redirect edit (Fixes #309, #314)

## 3.6.5.1 - 2020-05-22
### Fixed
- Fix missing order column install issue (Fixes #303)

## 3.6.5 - 2020-05-18
### Added
- Add redirect ordering (Closes #274)

## 3.6.4 - 2020-04-15
### Fixed
- Fix JS error when `%` appeared in SEO snippet text

## 3.6.3 - 2019-12-18
### Improved
- Replace SVG's while keeping text intact (Fixes #197)

### Fixed
- Fix social images not falling back to default (Fixes #188, #247)
- Fix slug showing incorrect site URL (Fixes #237)
- Fix SEO snippet showing homepage slug (Fixes #270)
- Fix snippet issue when using protocol relative site URLs (Fixes #249, #239)
- Fix incorrect canonical URL when site is behind a CDN (Closes #268)
- Fix sitemap not filtering elements by type correctly (Fixes #275)

## 3.6.2 - 2019-08-05
### Improved
- Improve support for diacritics

### Fixed
- Fix an issue when legacy SEO data is just the title string
- Fix issues with slug on single entries (Fixes #233, #228)
- Fis issue with umlauts in optimisation checklist (Fixes #232)

## 3.6.1.1 - 2019-07-26
### Fixed
- Fix bulk redirect issue when add to all sites (Fixes #154)

## 3.6.1 - 2019-07-26
### Added
- Added date added column to redirects (Closes #160)

### Changed
- Redirects will also be triggered by `{% exit 404 %}` in templates (Fix #206 via @domstubbs)

### Improved
- Redirect add forms will no longer be reset after adding a redirect. Only but the URI fields will be cleared. 

### Fixed
- Fix bulk importing of redirects (Fixes #154)
- Fix social title not syncing for new entries
- Fix snippet url preview bug (Fix #196 via @domstubbs)
- Fix redirect uri case sensitivity issue with MySQL (Fixes #116)

## 3.6.0 - 2019-07-25
### Added
- Add OG image width & height meta tags (Fixes #220)

### Changed
- Update minimum Craft CMS requirement to 3.2.x
- Social titles and descriptions can now be edited independently of the main snippet (Fixed #185)

### Fixed
- Fix entry preview error on keyword checklist (Fixes #224)
- Fix SEO field error when changing entry types (Fixes #215)
- Fix entire image meta data being saved with SEO field (Fixes #142)
- Fix sitemap error when SEO field doesn't have any advanced data (Fixes #96)
- Fix keyword checklist reading title length incorrectly (Fixes #199)
- Fix social meta outputting encoded characters (Fixes #198)
- Fix social description not updating correctly (Fixes #213)
- Fix multi-word keyword not checking slug correctly (Fixes #152)
- Fix CraftQL integration (Fixes #187)
- Fix SEO field erring in globals (Fixes #226)
- Fix section and type not being available in tokens (Fixes #221)

### Improved
- Improve alt text judgment in keyword checklist
- Check for keyword in first paragraph of body, main, and article tags
- Improve page meta field instructions in SEO settings
- Ensure canonical is always an absolute URL (Fixes #202)

## 3.5.4 - 2019-01-28

### Added
- Added support for Solspace Calendar. [#184]

### Fixed
- Fixed upgrade not correctly carrying over custom titles. [#183]

[#184]: https://github.com/ethercreative/seo/issues/184
[#183]: https://github.com/ethercreative/seo/issues/183

## 3.5.3 - 2019-01-23

### Fixed
- Fixed undefined index error when creating a new element containing an SEO field. [#176]
- Fixed error when using the SEO field in a category. [#177]
- Fixed sitemap undefined index error. [#178]

[#176]: https://github.com/ethercreative/seo/issues/176
[#177]: https://github.com/ethercreative/seo/issues/177
[#178]: https://github.com/ethercreative/seo/issues/178

## 3.5.2 - 2019-01-22

### Changed
- Increased debounce timeout when watching for input changes in fields.

### Fixed
- Fixed special characters being unnecessarily encoded when being out put in meta. [#173]
- Fixed JS error in SEO snippet when the entry doesn't have a slug.
- Excess whitespace removed from social title when dynamically updated.
- Fixed tokens sometimes showing as empty incorrectly.
- Fixed number of SEO snippet token re-renders from increasing exponentially. [#175]

[#173]: https://github.com/ethercreative/seo/issues/173
[#175]: https://github.com/ethercreative/seo/issues/175

## 3.5.1 - 2019-01-21

### Added
- Added console command to manually trigger the upgrade to the new data format
`./craft seo/upgrade/to-new-data-format`.

### Fixed
- Fixed issue when updating an SEO field without a suffix.
- Fixed readonly property error when running the upgrade task.

## 3.5.0 - 2019-01-21

> {warning} This update changes how SEO meta is stored. We **STRONGLY** 
recommend backing up your site before installing this update.

> {warning} This update contains some potentially breaking changes. If you use a 
custom `meta.twig` template you should review the 
changes [here](https://github.com/ethercreative/seo/commits/v3/src/templates/_seo/meta.twig).

### Added
- Advanced option for overriding the canonical URL.
- Added `rel="canonical"` header

### Changed
- Craft 3.1.0 is now required.
- The SEO field now uses a token-based system for the title, allowing for only 
certain parts of the title to be editable and adding twig support for pre-filling 
from fields! (Existing meta will be automatically upgraded)
- The SEO field description can now be pre-filled using twig.
- Robots meta tags are now rendered in the SEO meta. Useful for statically 
cached sites!
- Social titles and descriptions are no longer editable. Images are still 
editable. Improved social meta management is planned for a future update.

### Fixed
- SEO meta now correctly renders across multi-sites and locales.
- Fixed keyword checklist compatibility issue in Craft 3.1.
- Locale - replace w/ _ [#143]
- Fixed incorrect og:site_name [#139]

### Improved
- The social image "no volume" warning now explicitly states the need for 
volumes with public URLs [#115]
- Better handling of robots header.
- Whitespace now trimmed from title when checking SEO score (via [@Rias500])

[#115]: https://github.com/ethercreative/seo/issues/115
[#143]: https://github.com/ethercreative/seo/issues/143
[#139]: https://github.com/ethercreative/seo/issues/139
[@Rias500]: https://github.com/Rias500

## 3.4.4 - 2018-09-10
### Fixed
- Fixed a server error when generating the sitemap sub-maps.

## 3.4.3 - 2018-09-07
### Fixed
- Redirects page no longer throws a twig error if there are no redirects for all sites. [#134]

[#134]: https://github.com/ethercreative/seo/issues/134

## 3.4.2 - 2018-09-07
### Fixed
- Fixed a bug when saving a new element with an SEO field.


## 3.4.1 - 2018-09-06
### Fixed
- Fixed a bug when adding a redirect to all sites.

## 3.4.0 - 2018-09-06

> {warning} This update contains some potentially breaking changes. If you use a custom `meta.twig` template you should review the changes [here](https://github.com/ethercreative/seo/commits/v3/src/templates/_seo/meta.twig).

### Added
- Added `getSeoField($handle = 'seo')` Twig function for Site templates.
- Added global settings for Facebook App ID and Twitter handle.

### Changed
- SEO field data is now used via an `SeoData` model, rather than an array. **This may cause breaking changes, especially if you have a custom `meta.twig` template!**
- The default `robots.txt` now disallows all when the environment is NOT set to `'production'`. 
This will not have an effect for existing installs (where the SEO settings have been saved).
To manually update your `robots.txt`, replace the line `{% if craft.app.config.general.devMode %}` with `{% if craft.app.config.env != 'production' %}`. [#122]
- Redirects are no longer case-sensitive [#116]

### Fixed
- SEO no longer errors if a social image doesn't have a public url. [#131]
- Protocol relative image URLs are now handled correctly. [#125], [#126] (via [@monachilada])

### Improved
- The SEO meta field will now look for product and category elements when searching for the SEO field. [#128]
- Redirects now support full PCRE syntax. [#119], [#127]
- Redirects can now be specified on a per-site basis!

[#131]: https://github.com/ethercreative/seo/issues/131
[#122]: https://github.com/ethercreative/seo/issues/122
[#128]: https://github.com/ethercreative/seo/issues/128
[#127]: https://github.com/ethercreative/seo/issues/127
[#126]: https://github.com/ethercreative/seo/issues/126
[#125]: https://github.com/ethercreative/seo/issues/125
[#119]: https://github.com/ethercreative/seo/issues/119
[#118]: https://github.com/ethercreative/seo/issues/118
[#116]: https://github.com/ethercreative/seo/issues/116
[@monachilada]: https://github.com/monachilada

## 3.3.1 - 2018-09-03
### Fixed
- `craft.seo.custom` social images now fallback to the default social image from global settings. #113
- Backwards compatibility SEO v1 keywords
- Fixed bug where some requests would 404 when injecting 🤖's #130

### Changed
- SEO robots no longer return empty values #110
- Text in the keyword input will automatically be turned into a tag on blur #114

## 3.3.0 - 2018-05-25
**Heads up:** This update includes changes to the SEO `meta.twig`. If you are using a custom version you can review the changes [here](https://github.com/ethercreative/seo/commits/v3/src/templates/_seo/meta.twig).

### Fixed
- Fixed field settings throwing deprecation warning #109
- Fixed invalid sitemap urls
- Robot lightswitches now have a min-width #103
- Fixed TypeError in SEO variable when social image doesn't have a transform URL #101
- Fixed SEO hook throwing an error when a populated SEO field was removed from the current element #99

### Changed
- Redirect list moved below "Add Redirect" fields #107
- Empty / suffix only title fields will now populate with the element title

## 3.2.8 - 2018-05-16
### Fixed
- Fixed bug where `robots.txt` would not be accessible when not logged in. 

## 3.2.7 - 2018-05-10
**Heads up:** This update includes changes to the SEO `meta.twig`. If you are using a custom version you can review the changes [here](https://github.com/ethercreative/seo/commits/v3/src/templates/_seo/meta.twig).

### Fixed
- Fixed SEO field erroring after upgrade from Craft 2 #92
- Fixed sitemap erroring when entry doesn't have an SEO field #96

### Changed
- `og:site_name` now uses `seo.title` instead of `siteName` #95 (via @urbantrout)
- Canonical meta tag now uses `absoluteUrl` #94 (via @matthiaswh)
- Social URLs in `meta.twig` now use `absoluteUrl`

## 3.2.6 - 2018-05-01
### Fixed
- Fixed user permissions not being checked correctly #93

## 3.2.5 - 2018-04-26
### Fixed
- Fixed broken `robots.txt` code editor. #90
- Fixed sitemaps having wrong Content-Type header. #91

## 3.2.4 - 2018-04-16
### Added
- Added a "Use suffix as prefix" option to the SEO field settings. For *those* clients.

### Fixed
- The sitemap settings now show all sections, regardless of what sites they are enabled on.

## 3.2.3 - 2018-04-05
### Fixed
- Fixed bug where Craft would 500 error when robots were injected for an element without an expiry date (via @saylateam)
- Fixed a bug where the Craft edition referenced no longer existed [#81](https://github.com/ethercreative/seo/issues/81) (via @andris-sevcenko)

## 3.2.2 - 2018-03-16
### Fixed
- Fixed a bug where editing entries with an SEO field would error if the current site doesn't have a base URL. 

## 3.2.1 - 2018-03-15
### Fixed
- Fixed a bug that would cause Element API to error when no robots have been set.

## 3.2.0 - 2018-03-13
### Added
- You can now manage your sites 🤖 on a site-wide or per-field basis
	- The `X-Robots-Tag` header is added if you've set any robots
	- If the current entry has an expiry date, the `unavailable_after` directive will be added automatically
	- The `none` and `noimageindex` directives are automatically added to all pages when in `devMode`. No more accidental indexing of development sites!
- You can now manage your `robots.txt` file from the SEO settings!
	
### Changed
- Improved the settings page (now with 300% more tabs).

### Fixed
- Fixed sitemap dynamic urls throwing errors
- Sitemaps no longer show "headers already sent" warnings 
- Fixed the keywords checklist not realising content had changed on the page
- The keywords checklist now knows what spaces are and count words accordingly
- Fixed keywords checklist not working after live preview is opened
- Fixed bug where fallback social images would cause SEO field to error
- Keyword density now works correctly for keywords containing more than one word
- Fixed a bug where deleting a keyword would occasionally cause a JS error 

## 3.1.0 - 2018-03-02
### Added
- Added option to hide the social tab in the SEO field

### Changed
- It's now possible to pass social media meta to `craft.seo.custom`

### Fixed
- Fixed first paragraph check returning both positive and negative
- Snippet description now has minimum height

## 3.0.1 - 2018-01-23
### Fixed
- Fixed error when installing on Craft Personal

## 3.0.0 - 2018-01-23
### Changed
- Initial Craft 3 Release
