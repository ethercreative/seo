/* globals Craft */

import { debounce } from "../helpers";

export default class Snippet {
	
	constructor (namespace, SEO) {
		this.namespace = namespace;
		this.SEO = SEO;
		
		this.titleField = document.getElementById(`${namespace}Title`);
		this.slugField  = document.getElementById(`${namespace}Slug`);
		this.descField  = document.getElementById(`${namespace}Description`);
		
		this.SEO.snippetFields = {
			title: this.titleField,
			slug:  this.slugField,
			desc:  this.descField,
		};
		
		this.title();
		this.slugField && this.slug();
		this.desc();
	}
	
	/**
	 * Sync up the main title input with the SEO one
	 * (if it's a new entry, or we don't have a title)
	 *
	 * TODO: Alert the user if they change the main title, but not the SEO one
	 * TODO: If the SEO title matches the main title (sans suffix), keep syncing
	 */
	title () {
		const mainTitleField = document.getElementById("title");
		
		if (
			(
				!mainTitleField
				|| !this.titleField.classList.contains("clean")
				|| !this.SEO.options.isNew
			) && this.titleField.value.trim() !== ""
		) return;
		
		// This should be just the suffix (if there is one)
		const initial = this.titleField.value;
		
		const onMainTitleFieldInput = () => {
			this.titleField.value = `${mainTitleField.value} ${initial}`;
		};
		
		const onTitleInput = () => {
			this.titleField.classList.remove("clean");
			
			mainTitleField.removeEventListener(
				"input",
				onMainTitleFieldInput,
				false
			);
			
			this.titleField.removeEventListener("input", onTitleInput, false);
		};
		
		mainTitleField.addEventListener(
			"input",
			onMainTitleFieldInput,
			false
		);
		
		this.titleField.addEventListener("input", onTitleInput, false);
	}
	
	/**
	 * Sync up the SEO slug with crafts
	 * (Purely aesthetic, but nice to have)
	 */
	slug () {
		const mainSlugField = document.getElementById("slug");
		
		if (!mainSlugField) return;
		
		this.slugField.textContent = mainSlugField.value;
		
		const onSlugChange = () => {
			this.slugField.textContent = mainSlugField.value;
		};
		
		mainSlugField.addEventListener("input", onSlugChange);
		
		// Slug generation has a debounce that we need to account for to keep
		// the slugs in sync
		document.getElementById("title")
		        .addEventListener("input", debounce(onSlugChange, 500));
	}
	
	/**
	 * Adjust the height of the description TextArea to ensure it never scrolls,
	 * and handle descriptions that are longer than the recommended length.
	 */
	desc () {
		const adjustHeight = () => {
			setTimeout(() => {
				this.descField.style.height = "";
				this.descField.style.height = this.descField.scrollHeight + "px";
			}, 1);
		};
		
		// Prevent line breaks
		this.descField.addEventListener("keydown", e => {
			if (e.keyCode === 13) e.preventDefault();
		});
		
		// Cleanse line breaks & check length
		this.descField.addEventListener("input", () => {
			this.descField.value =
				this.descField.value.replace(/(\r\n|\r|\n)/gm, " ");
			
			if (this.descField.value.length > 160)
				this.descField.classList.add("invalid");
			else
				this.descField.classList.remove("invalid");
			
			adjustHeight();
		});
		
		// Adjust height TextArea size changes
		// ---------------------------------------------------------------------
		
		// On tab change
		if (document.getElementById("tabs")) {
			const tabs = document.querySelectorAll("#tabs a.tab");
			for (let i = 0; i < tabs.length; i++) {
				tabs[i].addEventListener("click", adjustHeight);
			}
		}
		
		// On open / close live preview
		if (Craft.livePreview) {
			Craft.livePreview.on("enter", adjustHeight);
			Craft.livePreview.on("exit", adjustHeight);
		}
		
		// On window resize
		window.addEventListener("resize", adjustHeight);
		
		// Set initial height
		adjustHeight();
	}
	
}