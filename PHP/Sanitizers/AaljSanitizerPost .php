<?php
namespace Isaac\Sanitizers;

use Isaac\Filters\Filter as Filter;
use Isaac\PreValidators\PreValidator as PreValidator;

/**
 * A class that manages cleaning input data for the contact page.
 */
class AaljSanitizerPost extends Sanitizer
{
    /* Properties */
    private $filesSanitizer = null;
    
    /* Constructor */
    public function __construct(PreValidator $preValidator, FilesSanitizer $filesSanitizer, Filter $filter)
    {
        $phpFFA = [
            'ajaxFlag'      => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                'flags'  => FILTER_REQUIRE_SCALAR],
            'nocache'       => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                'flags'  => FILTER_REQUIRE_SCALAR],
            'issue'         => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                'flags'  => FILTER_REQUIRE_SCALAR],
            'MAX_FILE_SIZE' => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                'flags'  => FILTER_REQUIRE_SCALAR],
            'aaljSubmitBtn' => ['filter' => FILTER_SANITIZE_STRING,
                                'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
            'token'         => ['filter' => FILTER_SANITIZE_STRING,
                                'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
        ];
        
        parent::__construct($preValidator, $filter, $phpFFA);
        $this->filesSanitizer = $filesSanitizer;
    }
    
    public function sanitize()
    {
        parent::sanitize();
        $this->filesSanitizer->sanitize();
        return;
    }
}
