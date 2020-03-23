<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class NewsletterPreValidatorPost extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_POST';
        $minElements = 7;
        $maxElements = 9;

        $inputMetaArray = [
                    'ajaxFlag'   =>   1,
                    'nocache'    =>   7,
                    'firstname'  =>  30,
                    'lastname'   =>  30,
                    'email1'     => 128,
                    'email2'     => 128,
                    'captcha'    =>   5,
                    'newsSubBtn' =>   9,
                    'token'      =>  32
        ];

        $transitoryInputs = ['ajaxFlag', 'nocache']; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
