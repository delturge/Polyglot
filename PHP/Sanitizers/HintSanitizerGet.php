<?php
namespace Isaac\Sanitizers;

use Isaac\Filters\Filter as Filter;
use Isaac\PreValidators\PreValidator as PreValidator;

/**
 * A class that manages cleaning input data for the password hint page.
 */
class HintSanitizerGet extends Sanitizer
{
    /* Properties */    

    /* Constructor */
    public function __construct(PreValidator $preValidator, Filter $filter)
    {
        $phpFFA = [
                    '1' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    '2' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    '3' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    '4' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    '5' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    '6' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
        ];
       
        parent::__construct($preValidator, $filter, $phpFFA);
    }
}
