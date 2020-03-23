<?php
require_once 'Cleaner.php';
/**
 * A class that manages cleaning $_FILES input data for the AALJ submit page.
 */
class AaljSanitizerFiles extends FilesSanitizer
{
    /* Properties */
    /* Constructor */
    public function __construct(PreValidator $preValidator, Filter $filter)
    {
        $phpFFA = [
                    'coverLetter' => [
                            'name'     => ['filter' => FILTER_SANITIZE_STRING,
                                           'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                            'type'     => ['filter' => FILTER_SANITIZE_STRING,
                                           'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                            'size'     => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                           'flags'  => FILTER_REQUIRE_SCALAR],
                            'tmp_name' => ['filter' => FILTER_SANITIZE_STRING,
                                           'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                            'error'    => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                           'flags'  => FILTER_REQUIRE_SCALAR]
                    ],
                    'manuscript' => [
                            'name'     => ['filter' => FILTER_SANITIZE_STRING,
                                           'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                            'type'     => ['filter' => FILTER_SANITIZE_STRING,
                                           'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                            'size'     => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                           'flags'  => FILTER_REQUIRE_SCALAR],
                            'tmp_name' => ['filter' => FILTER_SANITIZE_STRING,
                                           'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                            'error'    => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                           'flags'  => FILTER_REQUIRE_SCALAR]
                    ]
        ];

        parent::__construct($preValidator, $filter, $phpFFA);
    }
}
