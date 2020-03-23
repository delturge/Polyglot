<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * A class that manages cleaning input data for the contact page.
 */
class AaljPreValidatorPost extends PreValidator
{
    /* Properties */
    private $filesPreValidator = null;
    
    /* Constructor */
    public function __construct(array $inputArray, Encoder $encoder, PreValidator $filesPreValidator)
    {
        $inputSource = 'INPUT_POST';
        $minElements = 4;
        $maxElements = 6;

        $inputMetaArray = [
            'ajaxFlag'      =>   1,
            'nocache'       =>   7,
            'issue'         =>   1,
            'MAX_FILE_SIZE' =>   8,
            'aaljSubmitBtn' =>   6,
            'token'         =>  32,
        ];

        $transientInputs = ['ajaxFlag', 'nocache']; //Where "transient" means inputs may, or may not, succeed upon form submission.
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transientInputs, $encoder);
        $this->filesPreValidator = $filesPreValidator;
    }
    
    public function validate()  //Calls the "Five Horsemen" of pre-validation.
    {
        if (parent::validate() && $this->filesPreValidator->validate()) {
            return true;
        }

        throw new SecurityException("The HTTP request is malformed at the PreValidator level. HTML form or query string may have been tampered with.");
    }
}
