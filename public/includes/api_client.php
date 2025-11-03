<?php
/**
 * API Client for communicating with the Python API endpoint
 * This utility replaces direct database connections
 */

class APIClient {
    private $endpoint;
    private $timeout = 30;

    public function __construct($endpoint = null) {
        global $endpoint_server;
        $this->endpoint = $endpoint ?? ($endpoint_server ?? null);
        
        if (!$this->endpoint) {
            throw new Exception('API endpoint not configured. Set $endpoint_server in config.php');
        }
    }

    /**
     * Make a GET request to the API
     * @param string $path API endpoint path (e.g., '/api/schedule/requester_form')
     * @param array $params Query parameters
     * @return array Response data
     */
    public function get($path, $params = []) {
        $url = $this->buildUrl($path, $params);
        return $this->request('GET', $url);
    }

    /**
     * Make a POST request to the API
     * @param string $path API endpoint path
     * @param array $data Request body data
     * @return array Response data
     */
    public function post($path, $data = []) {
        $url = $this->endpoint . $path;
        return $this->request('POST', $url, $data);
    }

    /**
     * Make a PUT request to the API
     * @param string $path API endpoint path
     * @param array $data Request body data
     * @return array Response data
     */
    public function put($path, $data = []) {
        $url = $this->endpoint . $path;
        return $this->request('PUT', $url, $data);
    }

    /**
     * Make a DELETE request to the API
     * @param string $path API endpoint path
     * @param array $params Query parameters
     * @return array Response data
     */
    public function delete($path, $params = []) {
        $url = $this->buildUrl($path, $params);
        return $this->request('DELETE', $url);
    }

    /**
     * Execute the HTTP request
     * @param string $method HTTP method
     * @param string $url Full URL
     * @param array $data Request body data (for POST/PUT)
     * @return array Response data
     */
    private function request($method, $url, $data = null) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        // Handle SSL verification (set to false for development)
        if (strpos($url, 'https') === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        // Add request body for POST/PUT
        if ($data && in_array($method, ['POST', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("API Request failed: " . $error);
        }

        $decoded = json_decode($response, true);
        
        // Check for API errors
        if ($httpCode >= 400) {
            $message = isset($decoded['message']) ? $decoded['message'] : 'API Error: ' . $httpCode;
            throw new Exception($message);
        }

        return $decoded ?? [];
    }

    /**
     * Build URL with query parameters
     * @param string $path API path
     * @param array $params Query parameters
     * @return string Full URL with query string
     */
    private function buildUrl($path, $params = []) {
        $url = $this->endpoint . $path;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    /**
     * Query a specific table via API
     * This is a helper for simple queries
     * @param string $table Table name
     * @param array $where Where conditions (key => value pairs)
     * @return array Query results
     */
    public function query($table, $where = []) {
        $path = "/api/query/" . $table;
        return $this->get($path, $where);
    }
}

/**
 * Global API client singleton
 */
function get_api_client() {
    static $client = null;
    if ($client === null) {
        $client = new APIClient();
    }
    return $client;
}
?>
