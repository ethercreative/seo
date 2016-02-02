var ReadabilitySorter = Garnish.DragSort.extend(
{
	$readability: null,

	init: function(readability, settings)
	{
		this.$readability = $(readability);
		var $rows = this.$readability.children('.input').children(':not(.filler)');

		settings = $.extend({}, ReadabilitySorter.defaults, settings);

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
		helperClass: 'readabilityhelper'
	}
});