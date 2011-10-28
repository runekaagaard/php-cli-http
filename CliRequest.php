<?php

// Includes. HttpRequest2 needs to be installed via pear or be available on the
// include path.

require 'HTTP/Request2/Adapter.php';
require 'HTTP/Request2.php';

// Constants.
define('PHPCLIHTTP_FILEPATH_BASE', dirname(__FILE__));

/**
 * Adds posibility of faking requests on the cli to HTTP_Request2.
 */
class CliRequest extends HTTP_Request2 {
    /**
     * Defaults to CliAdapter, and enables the 'index_file' setting.
     * 
     * @param string $url
     * @param int $method
     * @param array $config 
     */
    public function __construct($url = null, $method = self::METHOD_GET, 
    array $config = array()) {
        if (empty($config['adapter'])) {
            $config['adapter'] = new CliAdapter();
        }
        $this->config['index_file'] = '';
        $this->checkConfig($config);
        parent::__construct($url, $method, $config);
    }
    
    /**
     * Validates that the index_file exists.
     * 
     * @param array $config 
     */
    private function checkConfig($config) {
        if (!is_file($config['index_file'])) {
            throw new HTTP_Request2_Exception(
                "File does not exist.", 
                HTTP_Request2_Exception::MISCONFIGURATION
            );
        }
    }
    
    /**
     * Shortcut to a get request.
     * 
     * @param string $path
     * @param array  $params
     * @param array  $class
     * @return HTTP_Request2_Response 
     */
    static function get($path, array $params=array(), $class=null) {
        $request = new $class($path, self::METHOD_GET);
        if ($params) {
            $path = $request->getUrl();
            $path->setQueryVariables($params);
        }
        return $request->send();
    }
    
    /** 
     * Shortcut to a post request.
     * 
     * @param string $path
     * @param array $data
     * @param array $params
     * @param type $class
     * @return HTTP_Request2_Response 
     */
    static function post($path, $data=array(), $params=array(), $class=null) {
        $request = new $class($path, self::METHOD_POST);
        if ($data) {
            $request->addPostParameter($data);
        }
        if ($params) {
            $url = $request->getUrl();
            $url->setQueryVariables($params);
        }
        return $request->send();
    }
    
    /*
     * Getters
     */
    
    public function getPostParams() {
        return $this->postParams;
    }
}