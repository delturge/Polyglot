<?php
namespace Isaac\Sanitizers;

use Isaac\Filters\Filter as Filter;
use Isaac\PreValidators\PreValidator as PreValidator;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class NewsletterUnsubscribeSanitizerPost extends Sanitizer
{
    /* Properties */    

    /* Constructor */
    public function __construct(PreValidator $preValidator, Filter $filter)
    {
        $phpFFA = [
                    'ajaxFlag'                 => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                                   'flags'  => FILTER_REQUIRE_SCALAR],
                    'nocache'                  => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                                   'flags'  => FILTER_REQUIRE_SCALAR],
                    'email1'                   => ['filter' => FILTER_SANITIZE_EMAIL,
                                                   'flags'  => FILTER_REQUIRE_SCALAR],
                    'newsletterUnsubscribeBtn' => ['filter' => FILTER_SANITIZE_STRING,
                                                   'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'token'                    => ['filter' => FILTER_SANITIZE_STRING,
                                                   'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
        ];
        
        parent::__construct($preValidator, $filter, $phpFFA);
    }
}
