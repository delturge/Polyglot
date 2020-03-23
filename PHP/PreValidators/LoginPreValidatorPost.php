<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class LoginPreValidatorPost extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_POST';
        $minElements = 6;  // 1)username, 2)password, 3)loginToken, 4)ajaxFlag, 5) button
        $maxElements = 6;

        $inputMetaArray = [
                        'ajaxFlag'   =>  1,
                        'nocache'    =>  7,
                        'username'   => 128,
                        'password'   => 50,
                        'loginBtn'   =>  5,
                        'loginToken' => 32
        ];

        $transitoryInputs = []; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
