<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the register page.
 */
class RegisterPreValidatorPost extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_POST';
        $minElements = 23;
        $maxElements = 25;

        $inputMetaArray = [
                    'ajaxFlag'        =>    1,
                    'nocache'         =>    7,
                    'firstname'       =>   30,
                    'lastname'        =>   30,
                    'email1'          =>  128,
                    'email2'          =>  128,
                    'phone'           =>   12,
                    'countryCode'     =>    7,
                    'extension'       =>    5,
                    'company'         =>   50,
                    'address'         =>  100,
                    'city'            =>   50,
                    'state'           =>    6,
                    'zip'             =>   10,
                    'country'         =>   50,
                    'jobLocation'     =>  100,
                    'timeToContact'   =>    1,
                    'contactPref'     =>    1,
                    'phoneType'       =>    1,
                    'subject'         =>    1,
                    'message'         => 1000,
                    'newsSubscribed' =>     1,
                    'captcha'         =>    5,
                    'registerBtn'     =>    8,
                    'token'           =>   32
        ];

        $transitoryInputs = ['ajaxFlag', 'nocache']; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
