<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class InitLoginPreValidatorPost extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_POST';
        $minElements = 4;
        $maxElements = 6;

        $inputMetaArray = [
                    'ajaxFlag'     =>  1,
                    'nocache'      =>  7,
                    'password'     => 50,
                    'username'     => 128,
                    'initLoginBtn' =>  5,
                    'token'        => 32
        ];

        $transitoryInputs = ['ajaxFlag', 'nocache']; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
