/* globals Craft */

export default function normalizeDiacritics (str) {
	// eslint-disable-next-line no-control-regex
	return str.replace(/[^\u0000-\u007E]/g, function (a) {
		return Craft.asciiCharMap[a] || a;
	});
}