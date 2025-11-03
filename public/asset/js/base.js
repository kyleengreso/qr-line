var protocol = window.location.protocol;
var host = window.location.host;
var realHost = protocol + '//' + host;

// Shared helper to build API URLs using the configured endpointHost emitted by PHP
// Returns null when endpointHost is not defined.
window.buildApiUrl = function(path, params) {
	if (typeof endpointHost === 'undefined' || !endpointHost) return null;
	try {
		const base = endpointHost.replace(/\/+$/, '');
		return base + path + (params ? ('?' + params.toString()) : '');
	} catch (e) {
		return null;
	}
};