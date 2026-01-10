<?php
class Router
{
    protected $currentController = 'AuthController'; // Default controller
    protected $currentMethod = 'login'; // Default method
    protected $params = [];

    public function __construct()
    {
        $url = $this->getUrl();

        // Convert underscore URLs to PascalCase (e.g., account_groups -> AccountGroups)
        if (isset($url[0])) {
            $controllerName = str_replace('_', ' ', $url[0]);
            $controllerName = ucwords($controllerName);
            $controllerName = str_replace(' ', '', $controllerName);
        } else {
            $controllerName = 'Auth';
        }

        // Look in controllers for first value
        if (file_exists('../app/controllers/' . $controllerName . 'Controller.php')) {
            $this->currentController = $controllerName . 'Controller';
            unset($url[0]);
        }

        require_once '../app/controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;

        // Check for second part of url
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // Get params
        $this->params = $url ? array_values($url) : [];

        // Call a callback with array of params
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl()
    {
        // 1. Try $_GET['url'] (Apache Rewrite)
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return array_values(array_filter($url));
        }

        // 2. Fallback: Parse REQUEST_URI (Built-in Server / Nginx)
        $request_uri = $_SERVER['REQUEST_URI'];

        // Remove query string
        if (false !== $pos = strpos($request_uri, '?')) {
            $request_uri = substr($request_uri, 0, $pos);
        }

        // Remove script name (e.g. /index.php) if present
        $script_name = $_SERVER['SCRIPT_NAME'];
        if (strpos($request_uri, $script_name) === 0) {
            $request_uri = substr($request_uri, strlen($script_name));
        } elseif (strpos($request_uri, dirname($script_name)) === 0) {
            $request_uri = substr($request_uri, strlen(dirname($script_name)));
        }

        $url = trim($request_uri, '/');

        if (!empty($url)) {
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return array_values(array_filter($url));
        }

        // Default
        return ['auth', 'login'];
    }
}
