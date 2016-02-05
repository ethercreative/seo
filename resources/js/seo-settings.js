var SeoSettings = function (namespace, run) {
	this.namespace = namespace;

	switch (run) {
		case 'sitemap':
			new SeoSettings.EditableTable(this.namespace + '-customUrls', this.namespace + '-addCustomUrl');
			break;
		case 'redirects':
			new SeoSettings.EditableTable(this.namespace + '-redirects', this.namespace + '-addRedirect');
			break;
		case 'settings':
			this.sitemapName();
			new SeoSettings.SortableList('#' + this.namespace + '-readability');
			break;
	}
};

// SITEMAP
SeoSettings.prototype.sitemapName = function () {
	var example = document.getElementById(this.namespace + '-sitemapNameExample');
	document.getElementById(this.namespace + '-sitemapName').addEventListener('input', function () {
		example.textContent = this.value + '.xml';
	});
};

// HELPERS
SeoSettings.EditableTable = function (tableId, addButtonId) {
	var self = this;
	this.table = document.getElementById(tableId).getElementsByTagName('tbody')[0];
	this.row = this.table.firstElementChild.cloneNode(true);
	this.table.firstElementChild.remove();

	this.row.classList.remove('hidden');

	[].slice.call(this.table.getElementsByClassName('delete')).forEach(function (el) {
		el.addEventListener('click', function () {
			el.parentNode.parentNode.remove();
		});
	});

	document.getElementById(addButtonId).addEventListener('click', function () {
		self.addRow();
	});
};

SeoSettings.EditableTable.prototype.addRow = function () {
	var i = 1;

	if (this.table.getElementsByTagName('tr').length > 0) {
		i = parseInt(this.table.lastElementChild.getAttribute('data-id')) + 1;
	}

	var newRow = this.row.cloneNode(true);
	newRow.setAttribute('data-id', i);
	newRow.innerHTML = newRow.innerHTML.replace(new RegExp('{i}', 'g'), i);

	newRow.getElementsByClassName('delete')[0].addEventListener('click', function () {
		newRow.remove();
	});

	this.table.appendChild(newRow);
};

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