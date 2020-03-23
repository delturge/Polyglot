<?php
declare(strict_types=1);

namespace tfwd\Sanitizers;

use tfwd\Sanitizers\Sanitizer as Sanitizer;
use tfwd\Exceptions\SanitizationException as SanitizationException;
use tfwd\Filters\Filter as Filter;
use tfwd\PreValidators\PreValidator as PreValidator;

/**
 * A class responsible for managing the filtering of
 * HTTP header input data.
 * 
 * @author Anthony E. Rutledge
 * @version 8-29-2018
 * @copyright (c) 2018, Time Flies Web Design
 */
class HttpRequestSanitizer extends Sanitizer implements RestMediaTypes
{
    protected static $instance;
    
    /* Properties */
    private $filteredExtElement;

    /**
     * A constructor that initializes a HttpRequestSanitizer object.
     * 
     * @param $preValidator The source of the inputData
     * @param $filter An object that contains the callback filter method for user-defined filters.
     * 
     * @return object HttpRequestSanitizer 
     */
    public function __construct(string $inputSource, array $inputData, Filter $utf8Filter, Filter $iso88591Filter)
    {
        $phpFFA = [ // PHP Field Filter Array
            'HTTP_REFERER'    => ['filter' => FILTER_SANITIZE_URL,
                                  'flags'  => FILTER_REQUIRE_SCALAR],
            'HTTP_ACCEPT'     => ['filter' => FILTER_SANITIZE_STRING,
                                  'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
            'HTTP_HOST'       => ['filter' => FILTER_SANITIZE_URL,
                                  'flags'  => FILTER_REQUIRE_SCALAR],
            'HTTP_USER_AGENT' => ['filter' => FILTER_SANITIZE_STRING,
                                  'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
            'REMOTE_ADDR'     => ['filter' => FILTER_SANITIZE_STRING,
                                  'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
            'REQUEST_METHOD'  => ['filter' => FILTER_SANITIZE_STRING,
                                  'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH], 
            'REQUEST_URI'     => ['filter' => FILTER_SANITIZE_STRING,
                                  'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
            'SERVER_PORT'     => ['filter' => FILTER_SANITIZE_STRING,
                                  'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH], 
            'SERVER_PROTOCOL' => ['filter' => FILTER_SANITIZE_STRING,
                                  'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
        ];

        parent::__construct($inputSource, $inputData, $phpFFA, $utf8Filter, $iso88591Filter);
    }

    /**
     * A method that makes a report string when errors are detected.
     * 
     * @param $phase string The current filter phase (1, 2, or 3)
     * @throws SanitizerException
     */
    private function isValidFilterElementResult(string $phase)  //May be obselete, not used any more.
    {
        if ($this->filteredExtElement === false || $this->filteredExtElement === null) {
            throw new SanitizationException("Phase $phase scalar filter failed on: " . $this->extInputScalar);
        }
    }

    /**
     * An override method that removes filters for transient HTTP properties,
     * where transient means a value appears only sometimes.
     * 
     * Example: HTTP_REFERER does not always appear in an HTTP GET request.
     */
    protected function prunePHPFieldFilters()
    {
        if (trim($this->inputArray['REQUEST_METHOD']) === self::GET) {
            unset($this->phpFieldFilterArray['HTTP_REFERER']); // Remove the filter instructions for it.
        }

        foreach (array_keys($this->phpFieldFilters) as $httpRequestProperty) {
            if (!isset($this->inputData[$httpRequestProperty])) {       // If the transitory HTTP property is not present.
                unset($this->phpFieldFilter[$httpRequestProperty]);     // Remove the filter instructions for it.
            }
        }
    }
}
?>