/* globals Craft */

/**
 * Snippet
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

export default class Social {
	
	constructor (namespace, SEO) {
		this.namespace = namespace;
		this.SEO = SEO;
		
		this.initImages();
	}
	
	// Initializers
	// =========================================================================
	
	initImages () {
		const imageInputs = document.querySelectorAll(
			`a[data-seo-social-image='${this.namespace}']`
		);
		
		let i = imageInputs.length;
		while (i--) {
			const imageInput = imageInputs[i];
			imageInput.addEventListener(
				"click",
				this.onImageInputClick.bind(this, imageInput)
			);
		}
	}
	
	// Events
	// =========================================================================
	
	onImageInputClick = (self, e) => {
		e.preventDefault();
		
		Craft.createElementSelectorModal(
			"Asset",
			{
				multiSelect: false,
				criteria: {
					kind: ['image'],
				},
				onSelect: elements => {
					const image = elements[0];
					self.style.backgroundImage = `url(${image.url})`;
					self.firstElementChild.value = image.id;
					self.lastElementChild.style.display = "none";
				},
			}
		);
	};
	
}
