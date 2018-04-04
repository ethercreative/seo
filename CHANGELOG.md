## Unreleased
### Fixed
- Fixed a bug where the Craft edition referenced no longer existed ([#81](https://github.com/ethercreative/seo/issues/81))

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
