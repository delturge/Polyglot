<?php
declare(strict_types=1);

namespace tfwd\PreValidators;

use tfwd\Encoders\Encoder as Encoder;

/**
 * A class responsible for managing the preliminary validation of
 * HTTP header input data.
 * 
 * @author Anthony E. Rutledge
 * @version 10-11-2018
 * @copyright (c) 2018, Time Flies Web Design
 */
class HttpRequestPreValidator extends PreValidator
{
    protected static $instance;
    
    public function __construct(array $rawData, Encoder $encoder)
    {
        $inputSource = '$_SERVER';
        $minElements = 30; // Can vary
        $maxElements = 70; // Can vary

        $rawDataMeta = [
            'HTTP_REFERER' => 1000,
            'HTTP_ACCEPT' => 9,
            'HTTP_HOST' => 9, // localhost
            'HTTP_USER_AGENT' => 512,
            'REMOTE_ADDR' => 45,
            'REQUEST_METHOD' => 6,
            'REQUEST_URI' => 1000,
            'SERVER_PORT' => 3,
            'SERVER_PROTOCOL' => 9
        ];

        $transientInputs = ['HTTP_REFERER']; //Where "transient" means an input element may, or may not, succeed (or be present).
        parent::__construct($inputSource, $rawData, $minElements, $maxElements, $rawDataMeta, $transientInputs, $encoder);
    }
}
?>