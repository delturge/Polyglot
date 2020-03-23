<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class HintPreValidatorGet extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_GET';
        $minElements = 6;
        $maxElements = 6;

        $inputMetaArray = [
                    '1' => 10,
                    '2' => 10,
                    '3' => 10,
                    '4' => 10,
                    '5' => 10,
                    '6' => 10
        ];

        $transitoryInputs = []; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
