<?php
namespace Isaac\Validators;

class AALJValidatorPost extends Validator
{
    /*Properties*/

    //Arrays
    private $tokens = null;
    private $files  = null;

    //Objects
    private $formTokenValidator = null;
    private $fileValidator      = null;

    //Blacklisting Regular Expressions
    private $aaljSubmitBtnRegex = '/(?>[^(\A\z)\p{C}\p{M}\p{N}\p{P}\p{S}\p{Z}0-9;\' %-])/u';

    /*Constructor*/
    public function __construct(Validator $formTokenValidator,  FileValidator $fileValidator, array $filteredInputArray)
    {
        $phpFVA = [
            'ajaxFlag'      => ['filter'  => FILTER_VALIDATE_INT,
                                'flags'   => FILTER_REQUIRE_SCALAR,
                                'options' => ['min_range' => 1, 'max_range' => 1]],
            'nocache'       => ['filter'  => FILTER_VALIDATE_INT,
                                'flags'   => FILTER_REQUIRE_SCALAR,
                                'options' => ['min_range' => 0, 'max_range' => 1000000]],
            'aaljSubmitBtn' => ['filter'  => FILTER_VALIDATE_REGEXP,
                                'flags'   => FILTER_REQUIRE_SCALAR,
                                'options' => ['regexp' => $this->aaljSubmitBtnRegex]]
        ];
        
        $phpFEMA = [
            'ajaxFlag'      => 'Illegal AJAX flag!',
            'nocache'       => 'Illegal nocache value!',
            'aaljSubmitBtn' => 'Bad button entry. Try again.'
        ];
        
        $validationMA = [
            'ajaxFlag'      => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 1, 'rangeOfValues' => null],
            'nocache'       => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1000000, 'pattern' => '/(?>\A[0-9]{1,7}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => null],
            'aaljSubmitBtn' => ['kind' => 'word', 'type' => 'string', 'min' => 4, 'max' => 4, 'pattern' => '/(?>\A(?:Submit){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 'Submit', 'rangeOfValues' => null]
        ];

        $validatorTargets = [
            'formTokenValidator' => ['token'],
        ];

        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInputArray, $validatorTargets);
        $this->formTokenValidator = $formTokenValidator;
        $this->fileValidator      = $fileValidator;
    }

    /*Accessors*/

    /*Helper Methods*/
    protected function isVoidInput()            //Test for blank form submission.
    {
        if(($this->filteredInputArray['firstname'] === '') && 
                ($this->filteredInputArray['lastname'] === '') && 
                ($this->filteredInputArray['email1'] === '') && 
                ($this->filteredInputArray['email2'] === '') &&
                ($this->filteredInputArray['phone'] === '') &&
//                ($this->filteredInputArray['institution'] === '') &&
                ($this->filteredInputArray['captcha'] === '') &&
                ($this->filteredInputArray['aaljSubmitBtn'] === 'Submit'))
        {
            $mandatoryFieldsArray     = ['firstname', 'lastname', 'email1', 'email2', 'captcha'];
            //$moreMandatoryFieldsArray = ['instituion'];
            $optionalFieldsArray      = ['phone'];

            //Provide error messages and for mandatory input fields (text, email, url, tel.
            foreach($mandatoryFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = 'You must fill this in.';
                $this->testResultsArray[$value]   = false;

                //Free up resources.
                $this->validationMetaArray[$value]       = null;
                $this->phpFieldValidatationArray[$value] = null;
                $this->phpFieldErrMsgsArray[$value]      = null;
                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
            }

            $mandatoryFieldsArray = null;
            unset($mandatoryFieldsArray);

            //Provide error messages and for mandatory fields.
//            foreach($moreMandatoryFieldsArray as $value)
//            {
//                $this->errorMessagesArray[$value] = 'You must make a selection.';
//                $this->testResultsArray[$value]   = false;
//                $this->filteredInputArray[$value] = '';
//                
//                //Free up resources.
//                $this->validationMetaArray[$value]       = null;
//                $this->phpFieldValidatationArray[$value] = null;
//                $this->phpFieldErrMsgsArray[$value]      = null;
//                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
//            }
//
//            $moreMandatoryFieldsArray = null;
//            unset($moreMandatoryFieldsArray);

            foreach($optionalFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = '' ;
                $this->testResultsArray[$value]   = true;
                
                //Free up resources.
                $this->validationMetaArray[$value]       = null;
                $this->phpFieldValidatationArray[$value] = null;
                $this->phpFieldErrMsgsArray[$value]      = null;
                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
            }

            //Free up resources.
            $this->validationMetaArray       = null;
            $this->phpFieldValidatationArray = null;
            $this->phpFieldErrMsgsArray      = null;
            $optionalFieldsArray             = null;
            unset($this->validationMetaArray, $this->phpFieldValidationArray, $this->phpFieldErrMsgsArray, $optionalFieldsArray);
            return true;
        }

        return false;
    }

    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = ($this->filteredInputArray + $this->fileValidator->getTranslatedInputArray());
    }

    protected function translateValidatedInput()  //Translate form inputs into database data.
    {
        $this->translatedInputArray = $this->filteredInputArray; 

        /**
         * Data translations.
         */

        //Remove unnecessary data from the translated array.
        $this->translatedInputArray['aaljSubmitBtn'] = null;
        $this->translatedInputArray['email2']        = null;
        unset($this->translatedInputArray['aaljSubmitBtn'], $this->translatedInputArray['email2']);
        return;
    }
    
    protected function validate()            //The wrapper for the task of validating form inputs.
    {      
        //---------FORM TOKEN VALIDATION---------------

        $this->tokens = $this->extractFilteredElements($this->filteredInputArray, $this->validatorTargets['formTokenValidator']);
        $this->formTokenValidator->setFilteredInputArray($this->tokens);

        //Free up resources.
        $this->tokens = null;
        unset($this->tokens);

        $this->formTokenValidator->validate();  //Failure is fatal.
        //----------------------------------------------
        
        if($this->isVoidInput()) { //Blank form submission test.
            return false;
        }

        /************ Use PHP FILTER functions to validate input.**************/
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);

        //Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);

        //Free up resources.
        $this->phpFieldErrMsgsArray = null;
        $phpFilterResults           = null;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);
        /**********************************************************************/

        /*******************Use personsal validation methods.******************/

        //Validate everything else using "variable" functions / methods.
        $this->coreValidatorLogic();

        //--------------------------------------

        //Validate Files: "coverLetter" and "manuscript".
        //$this->fileValidator->setFileProps($this->fileProps);
        //$this->fileValidator->setValidationMetaArray($this->filesValidationMetaArray);
        //$this->fileValidator->setFilteredInputArray($this->files);
        $this->fileValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->fileValidator, $this->files);

        //--------------------------------------

        /**********************************************************************/

        //Merge filtered input array with files input array.
        $this->mergeFilteredInputArrays();

        if(!in_array(false, $this->testResultsArray, true)) {
            $this->translateValidatedInput();
            return true;
        }

        return false;
    }

    /*Unique Field Validator Functions*/
    protected function aaljSubmitBtn($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }
}
