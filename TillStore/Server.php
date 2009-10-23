<?php
/**
 * TillStore/Server.php
 *
 * PHP Version 5
 *
 * @category   Database
 * @package    TillStore
 * @subpackage TillStore_Server
 * @author     Till Klampaeckel <till@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.html BSD License
 * @version    SVN: $Id$
 * @link       http://github.com/till/TillStore
 */

/**
 * HTTP server for TillStore.
 *
 * @category   Database
 * @package    TillStore
 * @subpackage TillStore_Server
 * @author     Till Klampaeckel <till@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.html BSD License
 * @version    Release: @package_version@
 * @link       http://github.com/till/TillStore
 */
class TillStore_Server
{
    protected $disconnectOpt = array('l_onoff' => 1, 'l_linger' => 1);

    protected $config;

    protected $tillIncomingSocket;
    protected $tillSocket;

    protected $tillStore;


    const ERR_NOTFOUND = 'TillStorex404';

    /**
     * Constructor
     *
     * @param array $config Config variables.
     *
     * @return $this
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Read configuration files, and merge. And return.
     *
     * @param string $etcDir The location of the configuration files.
     *
     * @return array
     */
    public static function readConfig($etcDir = '/etc/tillstore')
    {
        $defaultConfig = parse_ini_file($etcDir . '/default.ini');

        if (isset($defaultConfig['daemon']['socket'])) {
            $defaultConfig['daemon']['socket']
                = (bool) $defaultConfig['daemon']['socket'];
        }

        if (file_exists($etcDir . '/local.ini')
            && is_readable($etcDir . '/local.ini')
        ) {
            $localConfig = parse_ini_file($etcDir . '/local.ini');
        }

        $config = array_merge($defaultConfig, $localConfig);
        return $config;
    }

    /**
     * A very simple error handler -- should be used for debugging only.
     *
     * This is not static, so we can use {@link self::shutdown()} from it and free
     * all sockets.
     *
     * @param int    $errno   The error number
     * @param string $errstr  The error message
     * @param string $errfile The file the error was in
     * @param int    $errline The line the error was on
     *
     * @return void
     * @uses   self::shutdown()
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        echo "$errstr" . PHP_EOL;
        $this->shutdown();
        exit(1);
    }

    /**
     * Read incoming request.
     *
     * @return boolean
     * @uses   self::$tillIncomingSocket
     * @uses   self::shutdown()
     */
    public function handle()
    {
        $request = socket_read($this->tillIncomingSocket, 1024);
        if ($request === false) {
            return false;
        }
        $request = trim($request);
        if (!empty($request)) {

            if ($request == 'SHUTDOWN') { // super admin feature
                $this->shutdown();
            }

            $status = $this->parseRequest($request);
            if ($status === false) {
                $this->disconnectClient();
                return false;
            }
        }
        return true;
    }

    /**
     * Make the server listen for incoming request.
     *
     * @return mixed A resource (socket), or false.
     */
    public function listen()
    {
        return $this->tillIncomingSocket = socket_accept($this->tillSocket);
    }

    /**
     * Inject TillStore
     *
     * @param TillStore $tillStore An instance of TillStore
     *
     * @return $this
     */
    public function setTillStore(TillStore $tillStore)
    {
        $this->tillStore = $tillStore;
        return $this;
    }

    /**
     * Proper shutdown on all sockets.
     *
     * @return void
     * @uses   self::disconnectClient()
     */
    public function shutdown()
    {
        $this->disconnectClient();

        @socket_set_block($this->tillSocket);
        @socket_set_option(
            $this->tillSocket,
            SOL_SOCKET,
            SO_LINGER,
            $this->disconnectOpt
        );
    }

    /**
     * Create the server process.
     *
     * @return void
     */
    public function start()
    {
        $this->tillSocket = socket_create_listen($this->config['port']);
        if ($this->tillSocket === false) {
            echo "Error: " . socket_strerror(socket_last_error()) . PHP_EOL;
            exit(1);
        }
    }

    /**
     * If available, let's close the connection to the client.
     *
     * @return void
     * @uses   self::$tillIncomingSocket
     */
    public function disconnectClient()
    {
        if (is_resource($this->tillIncomingSocket)) {
            socket_close($this->tillIncomingSocket);
        }
    }

    /**
     * This is borrowed from HTTP_Server_Request
     *
     * @param string $command The command sent from the client.
     *
     * @return array
     * @see    self::parseRequest()
     */
    protected function parseHttpRequest($command)
    {
        $lines = explode("\r\n", $command);

        if (!preg_match("'([^ ]+) ([^ ]+) (HTTP/[^ ]+)'", $lines[0], $regs)) {
            return false;
        }
        $method   = $regs[1];
        $uri      = $regs[2];
        $protocol = $regs[3];


        // $this->writeResponse("Debug: {$method}, {$uri}, $protocol");

        $headers = array();

        for ($i = 1; $i < count($lines); $i++) {
            if (trim($lines[$i]) == '') {
                //empty line, after this the content should follow
                $i++;
                break;
            }
            $regs = array();
            if (preg_match("'([^: ]+): (.+)'", $lines[$i], $regs)) {
                $headers[(strtolower($regs[1]))]    =    $regs[2];
            }
        }
        //aggregate the content (POST data or so)
        $body = '';
        for ($i = $i; $i < count($lines); $i++) {
            $body .= $lines[$i] . "\r\n";
        }

        return array(
            'method'   => $method,
            'uri'      => $uri,
            'protocol' => $protocol,
            'headers'  => $headers,
            'body'     => $body,
        );
    }

    /**
     * This is ugly as hell.
     *
     * We run some validation on the command (aka the request), and parse it.
     *
     * After parsing, we attempt to save or retrieve it from the
     * {@link self::$tillStore}.
     *
     * @param string $command The command sent from the client.
     *
     * @return void
     * @uses   self::validateRequestVerb()
     * @uses   self::parseHttpRequest()
     */
    protected function parseRequest($command)
    {
        if (!$this->validateRequestVerb($command)) {
            $this->writeResponse("Unknown HTTP verb.");
            return false;
        }

        $request = $this->parseHttpRequest($command);

        // $this->writeResponse("YES, WE CAN.");

        switch ($request['method']) {
        default:
        case 'GET':
            $var = $this->tillStore->get($request['uri'], self::ERR_NOTFOUND);
            if ($var === self::ERR_NOTFOUND) {
                $this->writeResponse("Not found.", true);
            } else {
                $this->writeResponse($var);
            }
            break;

        case 'POST':
        case 'PUT':
            $body = '';
            if (!empty($request['body'])) {
                $body = trim($request['body']);
            }

            /*
            echo "BODY: $body"
                . " - "
                . var_export(empty($request['body']), true)
                . " - "
                . var_export($request, true) . PHP_EOL;
            */

            $this->tillStore->set($request['uri'], $body);
            $this->writeResponse("OK");
            break;
        }
        $this->disconnectClient();
    }

    /**
     * Is this a valid request, let's see?
     *
     * This function returns the verb or false. We currently support GET, POST
     * and PUT.
     *
     * @param string $command The command sent from the client.
     *
     * @return mixed False in case we don't support it, a string otherwise.
     * @see    self::parseRequest()
     */
    protected function validateRequestVerb($command)
    {
        $method = substr($command, 0, 4);
        $method = strtoupper(trim($method));

        switch ($method) {      
        case 'GET':
        case 'POST':
        case 'PUT':
            return $method;
            break;

        default:
            return false;
            break;
        }
    }

    /**
     * Write a response to the client.
     *
     * In case of an error, we expect to be "first" and send a 404 back.
     *
     * @param string $response Whatever we are supposed to send to the client
     *                         (in the body).
     * @param bool   $error    Is error, or not.
     *
     * @return void
     */
    protected function writeResponse($response, $error = false)
    {
        if ($error === true) {
            $errorCode = 404;
        } else {
            $errorCode = 200;
        }

        $responseHttp  = "HTTP/1.1 {$errorCode} OK" . PHP_EOL;
        $responseHttp .= 'Connection: close' . PHP_EOL;
        $responseHttp .= 'Date: ' . date('r') . PHP_EOL;
        $responseHttp .= 'Server: TillStore/@package_version@ (Linux)' . PHP_EOL;
        $responseHttp .= 'X-TillStore: ohai' . PHP_EOL;
        $responseHttp .= 'Content-Type: text/plain' . PHP_EOL . PHP_EOL;

        $errorMsg = '';
        if ($error === true) {

            if ($errorCode == 404) {
                $errorMsg .= 'Not found.';
            } else {
                $errorMsg .= 'Server Error.';
            }

            $errorMsg   .= PHP_EOL;

        }

        if (!empty($errorMsg)) {
            $responseHttp .= $errorMsg;
        } else {
            $responseHttp .= $response .= PHP_EOL;
        }

        socket_send(
            $this->tillIncomingSocket,
            $responseHttp,
            strlen($responseHttp),
            MSG_EOR
        );
        return;
    }
}