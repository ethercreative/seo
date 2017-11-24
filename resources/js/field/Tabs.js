/**
 * SEO Tabs
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     1.5.0
 */
export default class Tabs {
	
	// Variables
	// =========================================================================
	
	activeTab = { tab: null, page: null };
	pages = {};
	
	// Tabs
	// =========================================================================
	
	constructor (namespace) {
		this.namespace = namespace;
		
		// Variables
		const tabs = [].slice.call(
			document.getElementById(this.namespace + 'Tabs')
			        .getElementsByTagName('a')
		);
		
		this.pages = [].slice.call(
			document.getElementById(this.namespace + 'Pages').children
		).reduce((a, b) => {
			a[b.dataset.seoTab] = b;
			return a;
		}, {});
		
		// Set default active
		this.setActiveTab(tabs[0]);
		
		// Events
		tabs.forEach(tab => {
			tab.addEventListener('click', e => {
				e.preventDefault();
				this.setActiveTab(tab);
			});
		});
	}
	
	// Misc
	// =========================================================================
	
	setActiveTab (tab) {
		const name = tab.dataset.seoTab;
		
		if (this.activeTab.tab) {
			this.activeTab.tab.classList.remove('active');
			this.activeTab.page.classList.remove('active');
		}
		
		this.activeTab = {
			tab,
			page: this.pages[name],
		};
		
		this.activeTab.tab.classList.add('active');
		this.activeTab.page.classList.add('active');
	}
	
}
