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
		this.initDesc();
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
			imageInput.querySelector(".remove").addEventListener(
				"click",
				this.onImageRemoveClick.bind(this, imageInput)
			);
		}
	}
	
	initDesc () {
		const descInputs = document.querySelectorAll(
			`textarea[data-seo-social-desc='${this.namespace}']`
		);
		
		let i = descInputs.length;
		while (i--) {
			const desc = descInputs[i];
			desc.addEventListener("input", () => {
				// Replace line-breaks with spaces
				desc.value = desc.value.replace(/(\r\n|\r|\n)/gm, " ");
			});
			desc.addEventListener("keydown", e => {
				// Prevent line-breaks
				if (e.keyCode === 13) e.preventDefault();
			});
		}
	}
	
	// Events
	// =========================================================================
	
	onImageInputClick = (self, e) => {
		e.preventDefault();
		
		if (
			self.classList.contains("has-image")
			|| e.target.classList.contains("remove")
		) return;
		
		Craft.createElementSelectorModal(
			"craft\\elements\\Asset",
			{
				multiSelect: false,
				criteria: {
					kind: ['image'],
				},
				onSelect: elements => {
					const image = elements[0];
					self.classList.add("has-image");
					self.style.backgroundImage = `url(${image.url})`;
					self.firstElementChild.value = image.id;
				},
			}
		);
	};
	
	onImageRemoveClick = self => {
		if (!self.classList.contains("has-image")) return;
		
		self.classList.remove("has-image");
		self.style.backgroundImage = "";
		self.firstElementChild.value = "";
	};
	
}
