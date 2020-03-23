<?php
namespace Isaac\Sanitizers;

use Isaac\Filters\Filter as Filter;
use Isaac\PreValidators\PreValidator as PreValidator;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class InitLoginSanitizerGet extends Sanitizer
{
    /* Properties */    

    /* Constructor */
    public function __construct(PreValidator $preValidator, Filter $filter)
    {
        $phpFFA = [
                    'a' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'b' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'c' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'd' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'e' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'f' => ['filter' => FILTER_SANITIZE_STRING,
                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
                  ];

        parent::__construct($getSanitizer, $minFilterElements, $maxFilterElements, $maxCCA, $phpFFA, $transitoryInputs);
    }
}
