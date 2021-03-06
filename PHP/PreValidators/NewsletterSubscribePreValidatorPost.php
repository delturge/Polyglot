<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages pre-validating input data for the newsletter subscribe page.
 */
class NewsletterSubscribePreValidatorPost extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_POST';
        $minElements = 3;
        $maxElements = 5;

        $inputMetaArray = [
                    'ajaxFlag'               =>   1,
                    'nocache'                =>   7,
                    'email1'                 => 128,
                    'newsletterSubscribeBtn' =>   9,
                    'token'                  =>  32
        ];

        $transitoryInputs = ['ajaxFlag', 'nocache']; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
