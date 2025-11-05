var protocol = window.location.protocol;
var host = window.location.host;
var realHost = protocol + '//' + host;

window.getEndpointHost = function() {
	return (typeof window.endpointHost === 'string') ? window.endpointHost : '';
};

// Shared helper to build API URLs using the configured endpointHost emitted by PHP
// Returns null when endpointHost is not defined.
window.buildApiUrl = function(path, params) {
	var hostValue = window.getEndpointHost();
	if (!hostValue) return null;
	try {
		const base = hostValue.replace(/\/+$/, '');
		return base + path + (params ? ('?' + params.toString()) : '');
	} catch (e) {
		return null;
	}
};