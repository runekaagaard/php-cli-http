<?php

/**
 * A HTTP_Request2 Adapter that allows for faking requests on the command line.
 */
class CliAdapter extends HTTP_Request2_Adapter {

  /**
   * Returns the next response from the queue built by addResponse()
   *
   * If the queue is empty it will return default empty response with status 400,
   * if an Exception object was added to the queue it will be thrown.
   *
   * @param    HTTP_Request2
   * @return   HTTP_Request2_Response
   * @throws   Exception
   */
  public function sendRequest(HTTP_Request2 $request) {
      $environment = base64_encode(serialize($this->buildEnvironment($request)));
      $file = $request->getConfig('index_file');
      $dir = PHPCLIHTTP_FILEPATH_BASE;
      $command = "$dir/sendrequest.php $file $environment";
      exec($command, $output, $return_var);
      if ($return_var !== 0) {
          die("Something went wrong with the request.");
      }
      $output_as_string = trim(implode("\n", $output));
      $output_base64_decoded = base64_decode($output_as_string);
      $output_unserialized = unserialize($output_base64_decoded);
      $response = new HTTP_Request2_Response("HTTP/1.1 200 OK\r\n");
      $response->appendBody($output_unserialized['html']);
      return $response;
  }
  
  /**
   * Builds an array of post, get and server settings.
   * 
   * @return array
   */
  public function buildEnvironment(HTTP_Request2 $request) {
      $environment = array('_POST'=>array(), '_GET'=>array()
                           , '_SERVER'=>array());
      if ($request->getPostParams()) {
          $environment['_POST'] = $request->getPostParams();
      }
      $query = $request->getUrl()->getQuery();
      if (!empty($query)) {
        parse_str($query, $environment['_GET']);
      }
      $environment['_SERVER'] = 
          $this->getServerGlobal($request->getConfig('host'), 
              dirname($request->getConfig('index_file')), 
              $request->getUrl(), 
              $request->getMethod()
      );
      return $environment;
  }
  
  /**
   * Returns an array of the server settings.
   * 
   * @param string $host
   * @param string $document_root
   * @param object $url
   * @param string $method
   * @return array
   */
  public function getServerGlobal($host, $document_root, $url, $method) {
      $path = ltrim($url->getPath(), '/');
      $query = $url->getQuery();
      $server = array();
      $server['REDIRECT_STATUS'] = '200';
      $server['HTTP_HOST'] = $host;
      $server['HTTP_CONNECTION'] = 'keep-alive';
      $server['HTTP_CACHE_CONTROL'] = 'no-cache';
      $server['HTTP_PRAGMA'] = 'no-cache';
      $server['HTTP_ACCEPT'] = 'application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
      $server['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.51 Safari/534.3';
      $server['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate,sdch';
      $server['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.8';
      $server['HTTP_ACCEPT_CHARSET'] = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
      $server['HTTP_COOKIE'] = '';
      $server['PATH'] = '/usr/local/bin:/usr/bin:/bin';
      $server['SERVER_SIGNATURE'] = '<address>Apache/2.2.14 (Ubuntu) Server at '
          . $host . ' Port 80</address>
      ';
      $server['SERVER_SOFTWARE'] = 'Apache/2.2.14 (Ubuntu)';
      $server['SERVER_NAME'] = $host;
      $server['SERVER_ADDR'] = '127.0.1.1';
      $server['SERVER_PORT'] = '80';
      $server['REMOTE_ADDR'] = '127.0.1.1';
      $server['DOCUMENT_ROOT'] = $document_root;
      $server['SERVER_ADMIN'] = '[no address given]';
      $server['SCRIPT_FILENAME'] = $document_root . '/index.php';
      $server['REMOTE_PORT'] = '34237';
      $server['REDIRECT_URL'] = '/user/front';
      $server['GATEWAY_INTERFACE'] = 'CGI/1.1';
      $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
      $server['REQUEST_METHOD'] = $method;
      $server['QUERY_STRING'] = (!empty($query) ? "?$query" : '');
      $server['REQUEST_URI'] = '/' . $path . (!empty($query) ? "?$query" : '');
      $server['SCRIPT_NAME'] = '/index.php';
      $server['PHP_SELF'] = '/index.php';
      $server['REQUEST_TIME'] = date('U');
      return $server;
  }
}