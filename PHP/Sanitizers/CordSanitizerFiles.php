<?php
require_once 'Cleaner.php';
/**
 * A class that manages cleaning $_FILES input data for the AALJ submit page.
 */
class AALJCleanerFILES extends Cleaner
{
    /* Properties */
    /* Constructor */
    public function __construct(Sanitizer $sanitizer)
    {
        $minFileElements = 5;
        $maxFileElements = 5;

        //1024 bytes * 1024 bytes * 10 = 10485760 bytes = 10 Megabytes
        
        $maxFilesCCA = [
                            'coverLetter' => [
                                              'error'    =>        0,
                                              'size'     =>  2097152, //2MB
                                              'type'     =>       71,
                                              'name'     =>      255,
                                              'tmp_name' =>      255
                                             ],
                            'manuscript'  => [
                                              'error'    =>        0,
                                              'size'     => 10485760, //10MB
                                              'type'     =>       71,
                                              'name'     =>      255,
                                              'tmp_name' =>      255
                                             ],
                            'file3'       => [
                                              'error'    =>        0,
                                              'size'     =>  2097152,
                                              'type'     =>       15,
                                              'name'     =>      255,
                                              'tmp_name' =>      255
                                             ],
                            'file4'      => [
                                              'error'    =>        0,
                                              'size'     =>  2097152,
                                              'type'     =>       15,
                                              'name'     =>      255,
                                              'tmp_name' =>      255
                                             ],
                            'file5'      => [
                                              'error'    =>        0,
                                              'size'     =>  2097152,
                                              'type'     =>       15,
                                              'name'     =>      255,
                                              'tmp_name' =>      255
                                             ]
                       ];
        
        $phpFilesFFA  = [];
        
        foreach(array_keys($maxFilesCCA) as $file)
        {
            $phpFilesFFA[$file] = [
                                    'error'    => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                                   'flags'  => FILTER_REQUIRE_SCALAR],
                                    'size'     => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                                                   'flags'  => FILTER_REQUIRE_SCALAR],
                                    'type'     => ['filter' => FILTER_SANITIZE_STRING,
                                                   'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                                    'name'     => ['filter' => FILTER_SANITIZE_STRING,
                                                   'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
                                    'tmp_name' => ['filter' => FILTER_SANITIZE_STRING,
                                                   'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
                                  ];
        }

        $transitoryInputs = NULL; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        
        parent::__construct($sanitizer, $minFileElements, $maxFileElements, $maxFilesCCA, $phpFilesFFA, $transitoryInputs);
    }
}
?>