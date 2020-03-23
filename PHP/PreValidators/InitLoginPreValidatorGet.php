<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class InitLoginPreValidatorGet extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_GET';
        $minElements = 6;
        $maxElements = 6;

        $inputMetaArray = [
                    'a' => 160,
                    'b' => 160,
                    'c' => 160,
                    'd' => 160,
                    'e' => 160,
                    'f' => 160
        ];

        $transitoryInputs = []; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
