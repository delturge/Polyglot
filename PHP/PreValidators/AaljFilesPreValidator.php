<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning $_FILES input data for the AALJ submit page.
 */
class AaljFilesPreValidator extends FilesPreValidator
{
    /* Properties */
    
    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = '$_FILES';
        $minElements = 1;
        $maxElements = 2;
        $minFiles    = 0;
        $maxFiles    = 2;

        $maxFilesPerControl = [
            'coverLetter' => 1,
            'manuscript'  => 1
        ];

        $inputMetaArray = [
            'coverLetter' => [
                                'name'     =>      128,
                                'type'     =>       71,
                                'size'     =>  2097152,  //2MB
                                'tmp_name' =>      255,
                                'error'    =>        0
            ],
            'manuscript'  => [
                                'name'     =>      128,
                                'type'     =>       71,
                                'size'     => 10485760,  //1024 bytes * 1024 bytes * 10 = 10485760 bytes = 10 Megabytes
                                'tmp_name' =>      255,
                                'error'    =>        0
            ]
        ];

        $transientInputs = ['ajaxFlag', 'nocache', 'coverLetter', 'manuscript']; //Where "transient" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transientInputs, $encoder, $minFiles, $maxFiles, $maxFilesPerControl);
    }
}
