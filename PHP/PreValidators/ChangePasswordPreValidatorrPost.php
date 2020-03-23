<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the newsletter page.
 */
class ChangePasswordPreValidatorPost extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_POST';
        $minElements = 7;
        $maxElements = 9;

        $inputMetaArray = [
                    'ajaxFlag'          =>  1,
                    'nocache'           =>  7,
                    'password0'         => 50,
                    'password1'         => 50,
                    'password2'         => 50,
                    'pw_hint_question'  => 50,
                    'pw_hint'           => 50,
                    'changePasswordBtn' =>  6,
                    'token'             => 32
        ];

        $transitoryInputs = ['ajaxFlag', 'nocache']; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
