<?php
namespace Isaac\Sanitizers;

use Isaac\Filters\Filter as Filter;
use Isaac\PreValidators\PreValidator as PreValidator;

class ContactSanitizerPost extends Sanitizer
{
    /* Properties */    

    /* Constructor */
    public function __construct(PreValidator $preValidator, Filter $filter)
    {
        $phpFFA = [
                    'ajaxFlag'       => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                          'flags'  => FILTER_REQUIRE_SCALAR],
                    'nocache'        => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                          'flags'  => FILTER_REQUIRE_SCALAR],
                    'firstname'      => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'lastname'       => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'email1'         => ['filter' => FILTER_SANITIZE_EMAIL,
                                          'flags'  => FILTER_REQUIRE_SCALAR],
                    'email2'         => ['filter' => FILTER_SANITIZE_EMAIL,
                                          'flags'  => FILTER_REQUIRE_SCALAR],
                    'phone'          => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'countryCode'    => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'extension'      => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'company'        => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'address'        => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'city'           => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'state'          => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'zip'            => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'country'        => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'jobLocation'    => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'timeToContact'  => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                          'flags'  => FILTER_REQUIRE_SCALAR],
                    'contactPref'    => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                          'flags'  => FILTER_REQUIRE_SCALAR],
                    'phoneType'      => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                          'flags'  => FILTER_REQUIRE_SCALAR],
                    'subject'        => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                          'flags'  => FILTER_REQUIRE_SCALAR],
                    'message'        => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'newsSubscribed' => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                          'flags'  => FILTER_REQUIRE_SCALAR],
                    'captcha'        => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'contactBtn'     => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'token'          => ['filter' => FILTER_SANITIZE_STRING,
                                          'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
                 ];

        parent::__construct($preValidator, $filter, $phpFFA);
    }
}
