/**
 * Focus Keywords
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     1.5.0
 */
export default class FocusKeywords {
	
	constructor (namespace) {
		this.namespace = namespace;
		
		this.initInput();
	}
	
	// Keywords Input
	// =========================================================================
	
	initInput () {
		// Variables
		this.inputWrap =
			document.getElementById(this.namespace + "KeywordsInput");
		this.input = this.inputWrap.lastElementChild;
		
		// Events
		this.inputWrap.addEventListener("click", this.onInputWrapClick);
		this.input.addEventListener("focus", this.onInputFocus);
		this.input.addEventListener("blur", this.onInputBlur);
	}
	
	// Keywords Input: Events
	// -------------------------------------------------------------------------
	onInputWrapClick = e => {
		if (e.target === this.inputWrap)
			this.input.focus();
	};
	
	onInputFocus = () => {
		this.inputWrap.classList.add("focused");
	};
	
	onInputBlur = () => {
		this.inputWrap.classList.remove("focused");
	};
	
}