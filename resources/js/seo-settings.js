var SeoSettings = function (namespace) {
	this.namespace = namespace;

	// Sitemap
	this.sitemapName();
	this.customUrls();

	// Fieldtype
	new SeoSettings.SortableList('#' + this.namespace + '-readability');
};

// SITEMAP
SeoSettings.prototype.sitemapName = function () {
	var example = document.getElementById(this.namespace + '-sitemapNameExample');
	document.getElementById(this.namespace + '-sitemapName').addEventListener('input', function () {
		example.textContent = this.value;
	});
};

SeoSettings.prototype.customUrls = function () {
	var self = this;

	this.sitemapTable = document.getElementById(this.namespace + '-customUrls').getElementsByTagName('tbody')[0];
	this.sitemapRow = this.sitemapTable.firstElementChild.cloneNode(true);
	this.sitemapTable.firstElementChild.remove();

	this.sitemapRow.classList.remove('hidden');

	document.getElementById(this.namespace + '-addCustomUrl').addEventListener('click', function () {
		self.addCustomUrl();
	});
};

SeoSettings.prototype.addCustomUrl = function () {
	var i = 1;

	if (this.sitemapTable.getElementsByTagName('tr').length > 0) {
		i = parseInt(this.sitemapTable.lastElementChild.getAttribute('data-id')) + 1;
	}

	var newRow = this.sitemapRow.cloneNode(true);
	newRow.setAttribute('data-id', i);
	newRow.innerHTML = newRow.innerHTML.replace(new RegExp('{i}', 'g'), i);

	newRow.getElementsByClassName('delete')[0].addEventListener('click', function () {
		newRow.remove();
	});

	this.sitemapTable.appendChild(newRow);
};

// Redirects
// todo

// FIELDTYPE
// todo

// HELPERS
SeoSettings.SortableList = Garnish.DragSort.extend(
{
	$readability: null,

	init: function(readability, settings)
	{
		this.$readability = $(readability);
		var $rows = this.$readability.children('.input').children(':not(.filler)');

		settings = $.extend({}, SeoSettings.SortableList.defaults, settings);

		settings.container = this.$readability.children('.input');
		settings.helper = $.proxy(this, 'getHelper');
		settings.caboose = '.readabiltiy-row';
		settings.axis = Garnish.Y_AXIS;
		settings.magnetStrength = 4;
		settings.helperLagBase = 1.5;

		this.base($rows, settings);
	},

	getHelper: function($helperRow)
	{
		var $helper = $('<div class="'+this.settings.helperClass+'"/>').appendTo(Garnish.$bod);

		$helperRow.appendTo($helper);

		return $helper;
	}

},
{
	defaults: {
		handle: '.move',
		helperClass: 'sortablelisthelper'
	}
});