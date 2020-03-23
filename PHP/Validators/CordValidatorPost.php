<?php
namespace Isaac\Validators;

require_once 'Validator.php';
require 'TValidators.php';

class AALJValidatorPost extends Validator
{
    /* Traits */
    use TFortressContactValidators
    {
        phone as protected;
//        institution as protected;
    }

    /*Properties*/

    //Arrays
    private $tokens   = NULL;
    private $captchas = NULL;
    private $names    = NULL;
    private $emails   = NULL;
    private $files    = NULL;

    //PHP File Validation Arrays
    private $fileProps = [
                            'coverLetter' =>['minSize' => 1, 'maxSize' =>  2097152, 'mimeType' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']],
                            'manuscript'  =>['minSize' => 1, 'maxSize' => 10485760, 'mimeType' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']],
                            'file3'       =>['minSize' => 1, 'maxSize' =>  2097152, 'mimeType' => 'application/pdf'],
                            'file4'       =>['minSize' => 1, 'maxSize' =>  2097152, 'mimeType' => 'application/pdf'],
                            'file5'       =>['minSize' => 1, 'maxSize' =>  2097152, 'mimeType' => 'application/pdf']
                        ];

    private $filesValidationMetaArray = [];

    //Objects
    private $formTokenValidator = NULL;
    private $captchaValidator   = NULL;
    private $nameValidator      = NULL;
    private $emailValidator     = NULL;
    private $fileValidator      = NULL;

    //Blacklisting Regular Expressions
    private $aaljSubmitBtnRegex = '/(?>[^(\A\z)\p{C}\p{M}\p{N}\p{P}\p{S}\p{Z}0-9;\' %-])/u';
    private $phoneRegex         = '/(?>\A\z|[^\p{C}\p{Pc}\p{Pe}\p{Pf}\p{S}\p{Z}A-Za-z;\' %]){1}?/u';
/*    private $institutionRegex   = '/(?>\A\z|[^\p{C}_;%]){1}?/u';  */  

    /*Constructor*/
    public function __construct(Validator $formTokenValidator, Validator $captchaValidator, Validator $nameValidator, Validator $emailValidator, Validator $fileValidator, array $filteredPostInput, array $filteredFilesInput, array $transitoryInputs = NULL)
    {
        $phpFVA = [
                      'ajaxFlag'        => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 1, 'max_range' => 1]],
                      'nocache'         => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 0, 'max_range' => 1000000]],
                      'aaljSubmitBtn'   => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->aaljSubmitBtnRegex]],
                      'phone'           => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->phoneRegex]],
//                      'institution'         => ['filter'  => FILTER_VALIDATE_REGEXP,
//                                            'flags'   => FILTER_REQUIRE_SCALAR,
//                                            'options' => ['regexp' => $this->institutionRegex]]
                  ];

        $phpFEMA = [
                        'ajaxFlag'      => 'Illegal AJAX flag!',
                        'nocache'       => 'Illegal nocache value!',
                        'aaljSubmitBtn' => 'Bad button entry. Try again.',
                        'phone'         => 'Numbers (0-9) and dashes (-) only.',
//                        'institution'         => 'Illegal characters. Try again.'
                   ];

        $validationMA = [
                            'ajaxFlag'      => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 1, 'rangeOfValues' => NULL],
                            'nocache'       => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1000000, 'pattern' => '/(?>\A[0-9]{1,7}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL],
                            'aaljSubmitBtn' => ['kind' => 'word', 'type' => 'string', 'min' => 4, 'max' => 4, 'pattern' => '/(?>\A(?:Submit){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 'Submit', 'rangeOfValues' => NULL],
                            'phone'         => ['kind' => 'optphone', 'type' => 'string', 'min' => 12, 'max' => 12, 'pattern' => '/(?>\A[2-9]{1}?[0-9]{2}?\-{1}?[2-9]{1}?[0-9]{2}?\-{1}?[0-9]{4}?\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL]
/*                          'institution'        => ['kind' => 'opttitle', 'type' => 'string', 'min' => 1, 'max' => 100, 'pattern' => '/(?>\A[0-9A-Z\']{1}?[A-Za-z0-9:#@$&!?+\/.,\' -]{0,99}?){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL], */
                        ];

        $validatorTargets = [
                                'formTokenValidator' => ['token'],
                                'captchaValidator'   => ['captcha'],
                                'nameValidator'      => ['firstname', 'lastname'],
                                'emailValidator'     => ['email1', 'email2']
                            ];

        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredPostInput, $validatorTargets, $transitoryInputs);
        $this->formTokenValidator = $formTokenValidator;
        $this->captchaValidator   = $captchaValidator;
        $this->nameValidator      = $nameValidator;
        $this->emailValidator     = $emailValidator;
        $this->fileValidator      = $fileValidator;
        $this->files              = $filteredFilesInput;
        
        $this->filesValidationMetaArray['coverLetter']['error']    = ['kind' => 'integer', 'type' => 'number', 'min' => 0, 'max' => 0, 'noEmptyString' => true, 'specificValue' => 0, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['coverLetter']['size']     = ['kind' => 'integer', 'type' => 'number', 'min' => $this->fileProps['coverLetter']['minSize'], 'max' => $this->fileProps['coverLetter']['maxSize'], 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['coverLetter']['type']     = ['kind' => 'mimeType', 'type' => 'string', 'min' => 18, 'max' => 71, 'pattern' => '/(?>\Aapplication\/[a-z.-]\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => $this->fileProps['coverLetter']['mimeType']];
        $this->filesValidationMetaArray['coverLetter']['name']     = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z-_]{1,251}?(?>\.doc|.docx){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['coverLetter']['tmp_name'] = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z\/:\\]{2,255}\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];

        $this->filesValidationMetaArray['manuscript']['error']     = ['kind' => 'integer', 'type' => 'number', 'min' => 0, 'max' => 0, 'noEmptyString' => true, 'specificValue' => 0, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['manuscript']['size']      = ['kind' => 'integer', 'type' => 'number', 'min' => $this->fileProps['manuscript']['minSize'], 'max' => $this->fileProps['manuscript']['maxSize'], 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['manuscript']['type']      = ['kind' => 'mimeType', 'type' => 'string', 'min' => 18, 'max' => 71, 'pattern' => '/(?>\Aapplication\/[a-z.-]\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => $this->fileProps['manuscript']['mimeType']];
        $this->filesValidationMetaArray['manuscript']['name']      = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z-_]{1,251}?(?>\.doc|.docx){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['manuscript']['tmp_name']  = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z\/:\\]{2,255}\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        
        $this->filesValidationMetaArray['file3']['error']     = ['kind' => 'integer', 'type' => 'number', 'min' => 0, 'max' => 0, 'noEmptyString' => true, 'specificValue' => 0, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file3']['size']      = ['kind' => 'integer', 'type' => 'number', 'min' => $this->fileProps['file3']['minSize'], 'max' => $this->fileProps['file3']['maxSize'], 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file3']['type']      = ['kind' => 'mimeType', 'type' => 'string', 'min' => 15, 'max' => 15, 'pattern' => '/(?>\Aapplication\/[a-z.-]\z){1}?/u', 'noEmptyString' => true, 'specificValue' => $this->fileProps['manuscript']['mimeType'], 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file3']['name']      = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z-_]{1,251}?(?>\.pdf){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file3']['tmp_name']  = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z\/:\\]{2,255}\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        
        $this->filesValidationMetaArray['file4']['error']     = ['kind' => 'integer', 'type' => 'number', 'min' => 0, 'max' => 0, 'noEmptyString' => true, 'specificValue' => 0, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file4']['size']      = ['kind' => 'integer', 'type' => 'number', 'min' => $this->fileProps['file4']['minSize'], 'max' => $this->fileProps['file3']['maxSize'], 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file4']['type']      = ['kind' => 'mimeType', 'type' => 'string', 'min' => 15, 'max' => 15, 'pattern' => '/(?>\Aapplication\/[a-z.-]\z){1}?/u', 'noEmptyString' => true, 'specificValue' => $this->fileProps['manuscript']['mimeType'], 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file4']['name']      = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z-_]{1,251}?(?>\.pdf){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file4']['tmp_name']  = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z\/:\\]{2,255}\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        
        $this->filesValidationMetaArray['file5']['error']     = ['kind' => 'integer', 'type' => 'number', 'min' => 0, 'max' => 0, 'noEmptyString' => true, 'specificValue' => 0, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file5']['size']      = ['kind' => 'integer', 'type' => 'number', 'min' => $this->fileProps['file5']['minSize'], 'max' => $this->fileProps['file3']['maxSize'], 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file5']['type']      = ['kind' => 'mimeType', 'type' => 'string', 'min' => 15, 'max' => 15, 'pattern' => '/(?>\Aapplication\/[a-z.-]\z){1}?/u', 'noEmptyString' => true, 'specificValue' => $this->fileProps['manuscript']['mimeType'], 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file5']['name']      = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z-_]{1,251}?(?>\.pdf){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
        $this->filesValidationMetaArray['file5']['tmp_name']  = ['kind' => 'file', 'type' => 'string', 'min' => 1, 'max' => 255, 'pattern' => '/(?>\A[0-9A-Za-z\/:\\]{2,255}\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL];
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
                $this->validationMetaArray[$value]       = NULL;
                $this->phpFieldValidatationArray[$value] = NULL;
                $this->phpFieldErrMsgsArray[$value]      = NULL;
                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
            }

            $mandatoryFieldsArray = NULL;
            unset($mandatoryFieldsArray);

            //Provide error messages and for mandatory fields.
//            foreach($moreMandatoryFieldsArray as $value)
//            {
//                $this->errorMessagesArray[$value] = 'You must make a selection.';
//                $this->testResultsArray[$value]   = false;
//                $this->filteredInputArray[$value] = '';
//                
//                //Free up resources.
//                $this->validationMetaArray[$value]       = NULL;
//                $this->phpFieldValidatationArray[$value] = NULL;
//                $this->phpFieldErrMsgsArray[$value]      = NULL;
//                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
//            }
//
//            $moreMandatoryFieldsArray = NULL;
//            unset($moreMandatoryFieldsArray);

            foreach($optionalFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = '' ;
                $this->testResultsArray[$value]   = true;
                
                //Free up resources.
                $this->validationMetaArray[$value]       = NULL;
                $this->phpFieldValidatationArray[$value] = NULL;
                $this->phpFieldErrMsgsArray[$value]      = NULL;
                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
            }

            //Free up resources.
            $this->validationMetaArray       = NULL;
            $this->phpFieldValidatationArray = NULL;
            $this->phpFieldErrMsgsArray      = NULL;
            $optionalFieldsArray             = NULL;
            unset($this->validationMetaArray, $this->phpFieldValidationArray, $this->phpFieldErrMsgsArray, $optionalFieldsArray);
            return true;
        }

        return false;
    }

    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = ($this->names + $this->emails + $this->filteredInputArray + $this->fileValidator->getTranslatedInputArray());
    }

    protected function translateValidatedInput()  //Translate form inputs into database data.
    {
        $this->translatedInputArray = $this->filteredInputArray; 

        /**
         * Data translations.
         */

        //Remove unnecessary data from the translated array.
        $this->translatedInputArray['cordSubmitBtn'] = NULL;
        $this->translatedInputArray['email2']        = NULL;
        unset($this->translatedInputArray['cordSubmitBtn'], $this->translatedInputArray['email2']);
        return;
    }
    
    protected function myValidator()            //The wrapper for the task of validating form inputs.
    {      
        //---------FORM TOKEN VALIDATION---------------

        $this->tokens = $this->extractFilteredElements($this->filteredInputArray, $this->validatorTargets['formTokenValidator']);
        $this->formTokenValidator->setFilteredInputArray($this->tokens);

        //Free up resources.
        $this->tokens = NULL;
        unset($this->tokens);

        $this->formTokenValidator->validate();  //Failure is fatal.

        //----------CAPTCHA VALIDATION-----------------

        $this->captchas = $this->extractFilteredElements($this->filteredInputArray, $this->validatorTargets['captchaValidator']);
        $this->captchaValidator->setFilteredInputArray($this->captchas);

        //Free up resources.
        $this->captchas = NULL;
        unset($this->captchas);

        if(!$this->captchaValidator->validate())
        {
            return false;  //Short-circuit the validation.
        }

        //--------------------------------------------

        if($this->isVoidInput())  //Blank form submission test.
        {
            return false;
        }

        $this->names  = $this->extractFilteredElements($this->filteredInputArray, $this->validatorTargets['nameValidator']);
        $this->emails = $this->extractFilteredElements($this->filteredInputArray, $this->validatorTargets['emailValidator']);

        /************ Use PHP FILTER functions to validate input.**************/
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);

        //Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);

        //Free up resources.
        $this->phpFieldErrMsgsArray = NULL;
        $phpFilterResults           = NULL;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);
        /**********************************************************************/

        /*******************Use personsal validation methods.******************/

        //Validate everything else using "variable" functions / methods.
        $this->coreValidatorLogic();

        //--------------------------------------

        //Validate "firstname" and "lastname".
        $this->nameValidator->setFilteredInputArray($this->names);
        $this->nameValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->nameValidator, $this->names);

        //--------------------------------------

        //Validate "email1" and "email2".
        $this->emailValidator->setFilteredInputArray($this->emails);
        $this->emailValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->emailValidator, $this->emails);

        //--------------------------------------

        //Validate Files: "coverLetter" and "manuscript".
        $this->fileValidator->setFileProps($this->fileProps);
        $this->fileValidator->setValidationMetaArray($this->filesValidationMetaArray);
        $this->fileValidator->setFilteredInputArray($this->files);
        $this->fileValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->fileValidator, $this->files);

        //--------------------------------------

        /**********************************************************************/

        //Merge the names, emails, and other arrays together again.
        $this->mergeFilteredInputArrays();

        if(!in_array(false, $this->testResultsArray, true))
        {
            $this->translateValidatedInput();
            return true;
        }

        return false;
    }

    /*Unique Field Validator Functions*/
    protected function cordSubmitBtn($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }
}
?>