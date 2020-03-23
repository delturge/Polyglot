<?php
declare(strict_types=1);

namespace tfwd\Validators;

use tfwd\Exceptions\ValidationException as ValidationException;
use tfwd\Interfaces\RestMediaTypes as RestMediaTypes;

/**
 * A class for validating HTTP Requests
 * UNDER REFACTORYING / CONSTRUCTION
 * 
 * Note: The number of fields that need validation will expand. I just have not
 * made time to set in stone which values to care about. However, I am mirroring
 * the HTTP request RFC (and other things), closely.
 * 
 * @author Anthony E. Rutledge
 * @version 10-11-2018
 * @copyright (c) 2018, Time Flies Web Design
 */
class HttpRequestValidator extends Validator implements RestMediaTypes, HttpRequestTypes
{
    protected static $instance;

    /* Properties */
    private $referer;
    private $accept;
    private $protocol; 
    private $port;
    private $scheme;
    private $host;
    private $method;
    private $requestTarget;
    private $userAgent;
    private $ipAddress;

    // Blacklisting reqular expressions for fields with no explicit PHP validating filter.

    /* private $nameRegex = '/(?>[^(\A\z)\p{C}\p{N}\p{S}0-9;_% ])/u';       */
    /* private $httpHostRegex      = '/(?>\Awww\.[a-z]{21}?\.com\z){1}?/u'; */
    private $acceptRegex        = '/(?>[^(\A\z)\p{C}\p{S}])/u';
    private $hostRegex          = '/(?>[^(\A\z)\p{C}\p{S};_%\'\/* ])/u';
    private $userAgentRegex     = '/(?>[^(\A\z)\p{C}\p{S}])/u';
    private $methodRegex        = '/(?>[^(\A\z)\p{C}\p{N}\p{P}\p{S}\p{Z}0-9;_%\'\/* -])/u';
    private $requestTargetRegex = '/(?>[^(\A\z)\p{C}\p{S}\p{Z};%\'\"*_ ])/u';

    public function __construct(array $filteredData, array $transitoryInputs)
    {
        $phpFVA = [
            'HTTP_REFERER'    => ['filter'  => FILTER_VALIDATE_URL,
                                  'flags'   => FILTER_REQUIRE_SCALAR],
            'HTTP_ACCEPT'     => ['filter'  => FILTER_VALIDATE_REGEXP,
                                  'flags'   => FILTER_REQUIRE_SCALAR,
                                  'options' => ['regexp' => $this->acceptRegex]],
            'HTTP_HOST'       => ['filter'  => FILTER_VALIDATE_REGEXP,
                                  'flags'   => FILTER_REQUIRE_SCALAR,
                                  'options' => ['regexp' => $this->hostRegex]],
            'HTTP_USER_AGENT' => ['filter'  => FILTER_VALIDATE_REGEXP,
                                  'flags'   => FILTER_REQUIRE_SCALAR,
                                  'options' => ['regexp' => $this->userAgentRegex]],
            'REMOTE_ADDR'     => ['filter'  => FILTER_VALIDATE_IP, 
                                  'flags'   => FILTER_REQUIRE_SCALAR | FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6],
            'REQUEST_METHOD'  => ['filter'  => FILTER_VALIDATE_REGEXP,
                                  'flags'   => FILTER_REQUIRE_SCALAR,
                                  'options' => ['regexp' => $this->methodRegex]],
            'REQUEST_URI'     => ['filter'  => FILTER_VALIDATE_REGEXP,
                                  'flags'   => FILTER_REQUIRE_SCALAR,
                                  'options' => ['regexp' => $this->requestTargetRegex]]
        ];

        $phpFEMA = [
            'HTTP_REFERER'    => 'Bad URL for HTTP_REFERER.',
            'HTTP_ACCEPT'     => 'Bad Accept HTTP header value.',
            'HTTP_HOST'       => 'Bad URL for HTTP_HOST.',
            'HTTP_USER_AGENT' => 'Bad string for HTTP_USER_AGENT.',
            'REMOTE_ADDR'     => 'Bad IP address for REMOTE_ADDR.',
            'REQUEST_METHOD'  => 'Bad string for REQUEST_METHOD',
            'REQUEST_URI'     => 'Bad string for REQUEST_URI'
        ];

        $validationMA = [
            'HTTP_REFERER'    => ['optional' => false, 'kind' => 'url', 'type' => 'string', 'min' => 17, 'max' => 1000, 'pattern' => '#\A(?>http://localhost/[0-9a-z./?=&+-]{0,983}){1}?\z#u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null],
            /* 'HTTP_REFERER'    => ['optional' => false, 'kind' => 'url', 'type' => 'string', 'min' => 34, 'max' => 1000, 'pattern' => '#\A(?>https://www.anthonyerutledge.info/[0-9a-z./?=&+-]{0,966}){1}?\z#u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null], */
            /* 'HTTP_REFERER'    => ['optional' => false, 'kind' => 'url', 'type' => 'string', 'min' => 38, 'max' => 1000, 'pattern' => '#\A(?>https://www.fortressstabilization.com/[0-9a-z:./?=&+-]{0,962}){1}?\z#u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null], */
            'HTTP_ACCEPT'     => ['optional' => false, 'kind' => 'text', 'type' => 'string', 'min' => 9, 'max' => 16, 'pattern' => '/\A[0-9A-Za-z=\/*,;+. ]{9,16}?\z/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => ['text/html', 'application/json', 'application/xml', 'text/plain']],
            'HTTP_HOST'       => ['optional' => false, 'kind' => 'url', 'type' => 'string', 'min' => 9, 'max' => 9, 'pattern' => '/\A[a-z.]{9}?\z/u', 'noEmptyString' => true, 'specificValue' => 'localhost', 'rangeOfValues' => null],
            /* 'HTTP_HOST'       => ['optional' => false, 'kind' => 'url', 'type' => 'string', 'min' => 25, 'max' => 25, 'pattern' => '/\A[a-z.]{25}?\z/u', 'noEmptyString' => true, 'specificValue' => 'www.anthonyerutledge.info', 'rangeOfValues' => null], */
            //'HTTP_HOST'       => ['optional' => false, 'kind' => 'url', 'type' => 'string', 'min' => 29, 'max' => 29, 'pattern' => '/\A[a-z.]{29}?\z/u', 'noEmptyString' => true, 'specificValue' => 'www.fortressstabilization.com', 'rangeOfValues' => null],
            'HTTP_USER_AGENT' => ['optional' => false, 'kind' => 'userAgent', 'type' => 'string', 'min' => 28, 'max' => 512, 'pattern' => '/\A[0-9A-Za-z_,;:.\/\(\) -]{28,512}?\z/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => null], 
            'REMOTE_ADDR'     => ['optional' => false, 'kind' => 'ipAddress', 'type' => 'string', 'min' => 3, 'max' => 45, 'pattern' => '/\A(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)|(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]).){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]).){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))\z/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => null],
            'REQUEST_METHOD'  => ['optional' => false, 'kind' => 'word', 'type' => 'string', 'min' => 3, 'max' => 4, 'pattern' => '/\A(?>GET|POST){1}?\z/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => ['GET', 'POST']],
            'REQUEST_URI'     => ['optional' => false, 'kind' => 'path', 'type' => 'string', 'min' => 1, 'max' => 1000, 'pattern' => '/\A(?>\/{1}?[0-9A-Za-z]*?)+?(?>\?{1}?(?>[0-9A-Za-z]+?=[0-9A-Za-z+%]+?)+?)??\z/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => null]
        ];

        $validatorTargets;
        parent::__construct($filteredData, $phpFVA, $phpFEMA, $validationMA, $validatorTargets, $transitoryInputs);
    }

    /* Accessors */

    public function getAccept()
    {
        return $this->accept;
    }

    public function getProtocol()
    {
        return $this->protocol;
    }
    
    public function getPort()
    {
        return $this->port;
    }
    
    public function getScheme()
    {
        return $this->scheme;
    }
    
    public function getHost()
    {
        return $this->host;
    }

    public function getReferer()
    {
        return $this->referer;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getRequestTarget()
    {
        return $this->requestTarget;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function getUserIp()
    {
        return $this->userIp;
    }

    public function getQueryString()
    {
        return explode('?', $this->requestTarget)[1]; // Get 2nd element of the returned array.
    }

/******************************************************************************/

    protected function isVoidInput()
    {
        return  !isset($this->filteredData['HTTP_ACCEPT']) ||
                !isset($this->filteredData['HTTP_HOST']) ||
                !isset($this->filteredData['HTTP_USER_AGENT']) ||
                !isset($this->filteredData['REMOTE_ADDR']) ||
                !isset($this->filteredData['REQUEST_METHOD']) ||
                !isset($this->filteredData['REQUEST_URI']) ||
                !isset($this->filteredData['SERVER_PORT']) ||
                !isset($this->filteredData['SERVER_PROTOCOL']);
    }

    protected function translateData()
    {
        $this->cleanData['method'] = $this->filteredData['REQUEST_METHOD'];
        $this->cleanData['requestTarget'] = $this->filteredData['REQUEST_URI'];

        $httpSchemeAndVersion = explode('/', $this->filteredData['SERVER_PROTOCOL']);

        $this->cleanData['scheme'] = $httpSchemeAndVersion[0];
        $this->cleanData['version'] = (float) $httpSchemeAndVersion[1];
        $this->cleanData['ipAddress'] = $this->filteredData['REMOTE_ADDR'];
        $this->cleanData['port'] = $this->filteredData['SERVER_PORT'];
        $this->cleanData['host'] = $this->filteredData['HTTP_HOST'];
        $this->cleanData['userAgent'] = $this->filteredData['HTTP_USER_AGENT'];
        $this->cleanData['accept'] = $this->getMimeTypeFromAccept($this->filteredData['HTTP_ACCEPT']);
        
        $this->method = $this->cleanData['method'];
        $this->ipAddress = $this->cleanData['ipAddress'];
        $this->host = $this->cleanData['host'];
        $this->userAgent = $this->cleanData['userAgent'];

        if (isset($this->filteredData['HTTP_REFERER'])) {
            $this->cleanData['referer']= $this->filteredData['HTTP_REFERER'];
            return isset($this->referer, $this->accept, $this->host, $this->requestMethod, $this->requestUri, $this->userAgent, $this->userIp);
        }

        return isset($this->accept, $this->host, $this->requestMethod, $this->requestUri, $this->userAgent, $this->userIp);
    }

    private function freeUpResources()  // This should also kill the session.
    {
        $_SERVER = null;
        $_GET = null;
        $_POST = null;
        $_FILES = null;
        $_COOKIE = null;
        $_SESSION = null;
        $this->filteredInputArray = null;
        $this->testResultsArray = null;
        $this->validationMetaArray = null;
        $this->phpFieldValidatationArray = null;
        $this->phpFieldErrMsgsArray = null;
        unset($this->filteredInputArray, $this->testResultsArray, $this->validationMetaArray, $this->phpFieldValidationArray, $this->phpFieldErrMsgsArray);
    }

/******************************************************************************/

    /* Field Validator Functions */

    private function getMimeTypeFromAccept(string $accept)
    {
        $mimePosition = [];

        $mimePosition['html'] = mb_strpos($accept, self::HTML);
        $mimePosition['json'] = mb_strpos($accept, self::JSON);
        $mimePosition['xml'] = mb_strpos($accept, self::XML);
        $mimePosition['text'] = mb_strpos($accept, self::TEXT);
        
        if ($mimePosition['html'] !== false) {
            return self::HTML;
        }
        
        if ($mimePosition['json'] !== false) {
            return self::JSON;
        }
        
        if ($mimePosition['xml'] !== false) {
            return self::XML;
        }
        
        if ($mimePosition['text'] !== false) {
            return self::TEXT;
        }
        
        throw new ValidatonException('Unable to determine the perferred representational state for the HTTP response via the Accept header:!');
    }

    protected function HTTP_ACCEPT(string $value, array $validationMetaArray, string &$errorMessage)
    {
        extract($validationMetaArray);  // $kind, $optional, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues
        $mimeType = getMimeTypeFromAccept($value);
        return $this->validateInput($mimeType, $optional, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }

    protected function HTTP_HOST(string $value, array $validationMetaArray, string &$errorMessage)
    {
        extract($validationMetaArray);  // $kind, $optional, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($value, $optional, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }

    private function getRefererHostname(string $url) // Used in the fucntion below. //HEY CHANGE TO HTTPS BEFORE YOU GO LIVE!!!!!!!!!!
    {
        $scheme = 'https://';    // HEY CHANGE TO HTTPS BEFORE YOU GO LIVE!!!!!!!!!! REFACTOR!
        
        do {
            if (mb_strpos($url, $scheme) === 0 || mb_strpos($url, $scheme)) { //Find 'https://', if it exists.
                $url = trim(str_replace($scheme, '', $url));          //Remove https://
            } else {
                break;
            }
        } while(1);

        if (mb_strpos($url, '/') > 0) {               // Find '/', if it exists.
            return trim(mb_strstr($url, '/', true));  // Return everything before the first occurance of '/'.
        }  
    }

    protected function HTTP_REFERER(string $value, array $validationMetaArray, string &$errorMessage)
    {
        extract($validationMetaArray);  // $kind, $optional, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues

        if ($this->validateInput($value, $optional, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage)) {    
            if ($this->filteredInputArray['REQUEST_METHOD'] === self::POST) {
                if ($this->getRefererHostname($value) === $this->host) {  //HTTP_HOST has already been checked against SERVER_NAME
                    return true;
                }

                $errorMessage = "The hostname in the HTTP_REFERER does not match the one found in HTTP_HOST and SERVER_NAME.";
            }
            
            return true;
        }
        
        return false;
    }

    protected function HTTP_USER_AGENT(string $value, array $validationMetaArray, string &$errorMessage)
    {
        extract($validationMetaArray);  // $kind, $optional, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($value, $optional, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }

    protected function REMOTE_ADDR(string $value, array $validationMetaArray, string &$errorMessage)
    {
        extract($validationMetaArray);  // $kind, $optional, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValuess
        return $this->validateInput($value, $optional, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }

    protected function REQUEST_METHOD(string $value, array $validationMetaArray, string &$errorMessage)
    {
        extract($validationMetaArray);  // $kind, $optional, $type, $min, $max, $pattern, $specificValue, $rangeOfValues
        return $this->validateInput($value, $optional, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }

    protected function REQUEST_URI(string $value, array $validationMetaArray, string &$errorMessage)
    {
        extract($validationMetaArray);  // $kind, $optional, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($value, $optional, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }

    public function test()
    {       
        // Blank http request submission test.
        if ($this->isVoidInput()) {
            $this->freeUpResources();
            throw new ValidationException('The HTTP request had void values in INPUT_SERVER. Essential information missing.');
        }

        // Use PHP FILTER functions to validate input.
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);
        
        // Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);
        
        // Check for errors. $_SERVER validation errors are FATAL.
        if (in_array(false, $phpFilterResults, true)) {
            $this->freeUpResources();
            throw new ValidationException('The PHP filter validator for INPUT_SERVER has failed. ' . print_r($this->errorMessagesArray, true));
        }
        
        // Free up resources.
        $this->phpFieldErrMsgsArray = null;
        $phpFilterResults = null;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);
        
        // This wrapper method calls "variable functions" that validate each field.
        $this->coreValidatorLogic();

        // Check for errors. $_SERVER validation errors are FATAL.     
        if (!in_array(false, $this->testResultsArray, true) && $this->translateValidatedInput()) {
            $this->filteredInputArray = null;
            $this->testResultsArray = null;
            $this->validationMetaArray = null;
            $this->phpFieldValidatationArray = null;
            $this->phpFieldErrMsgsArray = null;
            unset($this->filteredInputArray, $this->testResultsArray, $this->validationMetaArray, $this->phpFieldValidationArray, $this->phpFieldErrMsgsArray);
            return true; // VALIDATION SUCCESS!!!!
        }
        
        // Something has gone wrong if code goes past this point.
        
        $this->freeUpResources();
        error_log(print_r($this->errorMessagesArray,true));
        
        // In other validators, this line would say 'return false'.
        throw new ValidationException('The HTTP request did not validate.');
    }
}
?>