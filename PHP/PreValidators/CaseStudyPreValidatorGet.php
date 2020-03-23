<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class CaseStudyPreValidatorGet extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_GET';
        $minElements = 3;
        $maxElements = 3;

        $inputMetaArray = [
                    'pane'      => 50,
                    'panel'     => 100,
                    'casestudy' => 32
        ];

        $transitoryInputs = []; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
