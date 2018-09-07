/**
 * SEO Settings
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2018
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     3.5.0
 */

export default class FieldType {

	constructor () {
		this.initSeoTitle();
	}

	// SEO Title
	// =========================================================================

	tokenList = null;

	initSeoTitle () {
		this.tokenList = document.getElementById("seoMetaTitle");

		const existingTokens = this.tokenList.querySelectorAll("li:not([data-static])");
		let i = existingTokens.length;
		if (i > 0) {
			while (--i) {
				const token = existingTokens[i];
			}
		}
	}

}