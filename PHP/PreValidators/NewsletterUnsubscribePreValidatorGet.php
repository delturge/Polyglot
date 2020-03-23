<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class NewsletterUnsubscribePreValidatorGet extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_GET';
        $minElements = 6;
        $maxElements = 6;

        $inputMetaArray = [
                    'a' => 96,
                    'b' => 96,
                    'c' => 96,
                    'd' => 96,
                    'e' => 96,
                    'f' => 96
        ];

        $transitoryInputs = NULL; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
