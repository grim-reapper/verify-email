<?php


namespace ImranAli\VerifyEmail;


use Illuminate\Contracts\Container\Container;
use ImranAli\VerifyEmail\Exceptions\verifyEmailException;

/**
 * Class to check email is valid or not.
 * Some code taken from Konstantin Grain class, Thanks to Konstantin Grain.
 * Class verifyEmail
 * @author Imran Ali
 * @package ImranAli\VerifyEmail
 */
class verifyEmail
{
    /**
     * Store stream response
     * @var bool
     */
    protected $stream = false;
    /**
     * SMTP port number
     * @var int
     */
    protected $port = 25;
    /**
     * email address to send request from
     * @var string
     */
    protected $from = 'root@localhost';
    /**
     * The connection timeout, in seconds
     * @var int
     */
    protected  $max_connection_timeout = 30;
    /**
     * Timeout value on stream, in seconds
     * @var int
     */
    protected $stream_timeout = 5;
    /**
     * Wait timeout on stream, in seconds
     * 0 - not to wait
     * @var int
     */
    protected $stream_timeout_wait = 0;
    /**
     * Where to throw exceptions for errors
     * @var bool
     */
    protected $exceptions = false;
    /**
     * The number of errors encountered
     * @var int
     */
    protected $error_count = 0;
    /**
     * Class debug output mode
     * @var bool
     */
    public $debug = false;
    /**
     * How to handle debug output.
     * Options:
     * `echo` output plain-text as is appropriate for cli
     * `html` output escaped, line breaks converted to <br> appropriate for browsers output
     * `log' output to error log as configured in php.ini
     * @var string
     */
    public $debug_output = 'echo';
    /**
     * Holds the most recent error message
     * @var string
     */
    public $errorInfo = '';
    /**
     * SMTP RFC standard line ending
     */
    const CRLF = "\r\n";

    /**
     * verifyEmail constructor.
     * set initial app configuration
     * @param  \Illuminate\Contracts\Container\Container  $app
     */
    public function __construct(Container $app)
    {
        $smtp_port      = $app['config']['verifyemail.smtp_port'];
        $from           = $app['config']['verifyemail.from_email'];
        $exceptions     = $app['config']['verifyemail.exceptions'];
        $debug          = $app['config']['verifyemail.debug'];
        $debug_output   = $app['config']['verifyemail.debug_output'];

        if(!empty($smtp_port)) {
            $this->port = $smtp_port;
        }
        if(!empty($from)) {
            $this->from = $from;
        }
        if(!empty($exceptions)) {
            $this->exceptions = $exceptions;
        }
        if(!empty($debug)) {
            $this->debug = $debug;
        }
        if(!empty($debug_output)) {
            $this->debug_output = $debug_output;
        }
    }

    /**
     * Set email address for SMTP request
     * @param string email $email email address
     *
     * @throws \ImranAli\VerifyEmail\Exceptions\verifyEmailException
     */
    public function setEmailFrom($email)
    {
        if(! $this->validate($email)) {
            $this->set_error('Invalid email address :'. $email);
            $this->edebug($this->errorInfo);
            if($this->exceptions) {
                throw new verifyEmailException($this->errorInfo);
            }
        }

        $this->from = $email;
    }

    /**
     * Validate email address pattern
     * @param $email
     *
     * @return bool
     */
    public function validate($email)
    {
        if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)){
            return false;
        }
        return true;
    }

    /**
     * Add an error message to error container
     * @param $msg
     */
    protected function set_error($msg)
    {
        $this->error_count++;
        $this->errorInfo = $msg;
    }

    /**
     * Check if an error occurred
     * @return bool
     */
    public function isError() {
        return ($this->error_count > 0);
    }

    /**
     * Output debug info
     * only generates if debug output is enabled
     * @param  string  $str
     */
    private function edebug(string $str)
    {
        if(! $this->debug) {
            return;
        }

        switch ($this->debug_output) {
            case 'log':
                // do not out anything just log it
                error_log($str);
                break;
            case 'html':
                // cleans up output for better looking
                echo htmlentities(
                    preg_replace('/[\r\n]+/','', $str), ENT_QUOTES, 'UTF-8'
                )."<br>\n";
                break;
            case 'echo':
                // just normlize line breaks
                $str = preg_replace('/(\r\n|\r|\n)/ms',"\n", $str);
                echo gmdate('Y-m-d H:i:s'). "\t". str_replace("\n","\n\t", trim($str))."\n";
        }
    }

    /**
     * Set connection timeout in seconds
     * @param  int  $seconds
     */
    public function setConnectionTimeout(int $seconds)
    {
        if($seconds > 0){
            $this->max_connection_timeout = $seconds;
        }
    }

    /**
     * Sets the timeout value on stream, express in the seconds
     * @param  int  $seconds
     */
    public function setStreamTimeout(int $seconds)
    {
        if($seconds > 0) {
            $this->stream_timeout = $seconds;
        }
    }

    /**
     * Sets time to wait for  stream, in seconds
     * @param  int  $seconds
     */
    public function setStreamTimeoutWait(int $seconds)
    {
        if($seconds > 0) {
            $this->stream_timeout_wait = $seconds;
        }
    }

    /**
     * Get array of MX records for host, sort by weight information
     * @param  string  $hostname the internet host name
     *
     * @return array
     */
    public function getMXrecords(string $hostname)
    {
        $mxhosts = array();
        $mxweights = array();

        if(getmxrr($hostname,$mxhosts, $mxweights) === FALSE) {
            $this->set_error("MX records not found or an error occured");
            $this->edebug($this->errorInfo);
        }else {
            array_multisort($mxweights, $mxhosts);
        }

        if(empty($mxhosts)) {
            $mxhosts[] = $hostname;
        }

        return $mxhosts;
    }

    /**
     * Parse input email to array (0 => user, 1=> domain)
     * @param $email
     * @param  bool  $only_domain
     *
     * @return array
     */
    protected function parseEmail($email, $only_domain = TRUE)
    {
        sscanf($email,"%[^@]@%s", $user, $domain);
        return ($only_domain) ? $domain : array($user,$domain);
    }

    /**
     * Main function to check up e-mail address
     * @param $email
     *
     * @return bool
     * @throws \ImranAli\VerifyEmail\Exceptions\verifyEmailException
     */
    public function checkEmail($email)
    {

        if(!$this->validate($email)) {
            $this->set_error("{$email} incorrect E-mail address");
            $this->edebug($this->errorInfo);
            if($this->exceptions) {
                throw new verifyEmailException($this->errorInfo);
            }

            return false;
        }

        $this->error_count = 0; // reset errors

        $mxs = $this->getMXrecords($this->parseEmail($email));
        $timeout = ceil($this->max_connection_timeout / count($mxs));

        foreach ($mxs as $host) {
            $this->stream = @stream_socket_client("tcp://". $host . ":" . $this->port, $errno, $errstr, $timeout);
            if($this->stream === FALSE) {
                if($errno == 0){
                    $this->set_error('Problem initializing the socket');
                    $this->edebug($this->errorInfo);
                    if($this->exceptions) {
                        throw new verifyEmailException($this->errorInfo);
                    }
                    return false;
                }else {
                    $this->edebug($host. ':' . $errstr);
                }
            }else {
                stream_set_timeout($this->stream, $this->stream_timeout);
                stream_set_blocking($this->stream, 1);

                if($this->_streamCode($this->_streamResponse()) === '220') {
                    $this->edebug("Connection success {$host}");
                    break;
                }else {
                    fclose($this->stream);
                    $this->stream = false;
                }
            }
        }

        if($this->stream === false) {
            $this->set_error('All connection failed');
            $this->edebug($this->errorInfo);
            if($this->exceptions) {
                throw new verifyEmailException($this->errorInfo);
            }
            return false;
        }

        $this->_streamQuery("HELO ". $this->parseEmail($this->from));
        $this->_streamResponse();
        $this->_streamQuery("MAIL FROM: <{$this->from}>");
        $this->_streamResponse();
        $this->_streamQuery("RCPT TO: <{$email}>");
        $code = $this->_streamCode($this->_streamResponse());

        $this->_streamQuery('RSET');
        $this->_streamQuery("QUIT");
        fclose($this->stream);

        switch ($code) {
            case '250':
            case '450':
            case '451':
            case '452':
                return true;
            default:
                return false;
        }

    }

    /**
     * Write the contents of string to the file stream pointed to by handle
     * if an error occurs return false
     * @param $query the string that is to be written
     *
     * @return int Returns a result code, as a integer.
     */
    protected function _streamQuery($query)
    {
        $this->edebug($query);
        return @stream_socket_sendto($this->stream, $query. self::CRLF);
    }

    /**
     * Reads all the line long the answer and analyze it.
     * if an error occurs, return false
     * @param  int  $timed
     *
     * @return string
     */
    protected function _streamResponse($timed = 0)
    {
        $reply = stream_get_line($this->stream, 1);
        $status = stream_get_meta_data($this->stream);

        if (!empty($status['timed_out'])) {
            $this->edebug("Timed out while waiting for data! (timeout {$this->stream_timeout} seconds)");
        }

        if ($reply === FALSE && $status['timed_out'] && $timed < $this->stream_timeout_wait) {
            return $this->_streamResponse($timed + $this->stream_timeout);
        }


        if ($reply !== FALSE && $status['unread_bytes'] > 0) {
            $reply .= stream_get_line($this->stream, $status['unread_bytes'], self::CRLF);
        }
        $this->edebug($reply);
        return $reply;
    }

    /**
     * Get response code from the response
     * @param $str
     *
     * @return string
     */
    protected function _streamCode($str)
    {
        preg_match('/^(?<code>[0-9]{3})(\s|-)(.*)$/ims', $str, $matches);
        $code = isset($matches['code']) ? $matches['code'] : false;
        return $code;
    }
}