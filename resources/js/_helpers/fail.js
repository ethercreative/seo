/* global Craft */
export default function fail (message) {
	Craft.cp.displayError(`SEO: ${message}`);
	window.console && console.error.apply( // eslint-disable-line no-console
		console,
		[
			`%cSEO: %c ${message}`,
			'font-weight: bold;',
			'font-weight: normal;',
		]
	);
}
