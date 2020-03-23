<?php
namespace Isaac\Sanitizers;

use Isaac\Filters\Filter as Filter;
use Isaac\PreValidators\PreValidator as PreValidator;

/**
 * A class that manages cleaning input data for the change password page.
 */
class ChangePasswordSanitizerPost extends Sanitizer
{
    /* Properties */    

    /* Constructor */
    public function __construct(PreValidator $preValidator, Filter $filter)
    {
        $phpFFA = [
                    'ajaxFlag'          => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                            'flags'  => FILTER_REQUIRE_SCALAR],
                    'nocache'           => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                            'flags'  => FILTER_REQUIRE_SCALAR],
                    'password0'         => ['filter' => FILTER_SANITIZE_STRING,
                                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'password1'         => ['filter' => FILTER_SANITIZE_STRING,
                                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'password2'         => ['filter' => FILTER_SANITIZE_STRING,
                                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'pw_hint_question'  => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                            'flags'  => FILTER_REQUIRE_SCALAR],
                    'pw_hint'           => ['filter' => FILTER_SANITIZE_STRING,
                                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'changePasswordBtn' => ['filter' => FILTER_SANITIZE_STRING,
                                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                    'token'             => ['filter' => FILTER_SANITIZE_STRING,
                                            'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
                 ];

        parent::__construct($preValidator, $filter, $phpFFA);
    }
}
