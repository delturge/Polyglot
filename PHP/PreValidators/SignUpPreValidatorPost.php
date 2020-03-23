<?php
declare(strict_types=1);

namespace tfwd\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the join page.
 */
class SignUpPreValidatorPost extends PreValidator
{
    /* Properties */    

    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder)
    {
        $inputSource = 'INPUT_POST';
        $minElements = 14;
        $maxElements = 16;

        $inputMetaArray = [
                    'ajaxFlag'     =>    1,
                    'nocache'      =>    7,
                    'memberType'   =>    1,
                    'memberOption' =>    1,
                    'payOption'    =>    1,
                    'orgName'      =>  150,
                    'firstname'    =>   30,
                    'lastname'     =>   30,
                    'email1'       =>  128,
                    'email2'       =>  128,
                    'phone'        =>   12,
                    'subject'      =>    1,
                    'message'      => 1000,
                    'captcha'      =>    5,
                    'joinBtn'      =>    4,
                    'token'        =>   32
        ];

        $transitoryInputs = ['ajaxFlag', 'nocache', 'orgName', 'joinBtn']; //Where "transitory" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transitoryInputs, $encoder);
    }
}
