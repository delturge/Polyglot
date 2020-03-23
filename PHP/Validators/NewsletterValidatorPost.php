<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class NewsletterValidatorPost extends Validator
{
    /*Properties*/

    //Arrays
    private $tokens   = NULL;
    private $captchas = NULL;
    private $names    = NULL;
    private $emails   = NULL;

    //Objects
    private $formTokenValidator = NULL;
    private $captchaValidator   = NULL;
    private $nameValidator      = NULL;
    private $emailValidator     = NULL;
    
    //Blacklisting Regular Expressions
    private $newsSubBtnRegex = '/(?>[^(\A\z)\p{C}\p{M}\p{N}\p{P}\p{S}\p{Z}0-9;\' %-])/u';

    /*Constructor*/
    public function __construct(Validator $formTokenValidator, Validator $captchaValidator, Validator $nameValidator, Validator $emailValidator, array $filteredInput, array $transitoryInputs = NULL)
    {                
        $phpFVA = [
                      'ajaxFlag'   => ['filter'  => FILTER_VALIDATE_INT,
                                       'flags'   => FILTER_REQUIRE_SCALAR,
                                       'options' => ['min_range' => 1, 'max_range' => 1]],
                      'nocache'    => ['filter'  => FILTER_VALIDATE_INT,
                                       'flags'   => FILTER_REQUIRE_SCALAR,
                                       'options' => ['min_range' => 0, 'max_range' => 1000000]],
                      'newsSubBtn' => ['filter'  => FILTER_VALIDATE_REGEXP,
                                       'flags'   => FILTER_REQUIRE_SCALAR,
                                       'options' => ['regexp' => $this->newsSubBtnRegex]]
                  ];
                
        $phpFEMA = [
                        'ajaxFlag'   => 'Illegal AJAX flag!',
                        'nocache'    => 'Illegal nocache value!',
                        'newsSubBtn' => 'Bad button entry. Try again.'
                   ];
        
        $validationMA = [
                            'ajaxFlag'   => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 1, 'rangeOfValues' => NULL],
                            'nocache'    => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1000000, 'pattern' => '/(?>\A[0-9]{1,7}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL],
                            'newsSubBtn' => ['kind' => 'word', 'type' => 'string', 'min' => 9, 'max' => 9, 'pattern' => '/(?>\A(?:Subscribe){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  'Subscribe', 'rangeOfValues' => NULL]
                        ];
        
        $validatorTargets = [
                                'formTokenValidator' => ['token'],
                                'captchaValidator'   => ['captcha'],
                                'emailValidator'     => ['email1', 'email2'],
                                'nameValidator'      => ['firstname', 'lastname']
                            ];
        
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInput, $validatorTargets, $transitoryInputs);
        $this->formTokenValidator = $formTokenValidator;
        $this->captchaValidator   = $captchaValidator;
        $this->nameValidator      = $nameValidator;
        $this->emailValidator     = $emailValidator;
    }
    
    protected function isVoidInput()            //Test for blank form submission.
    {
        if(($this->filteredInputArray['firstname'] === '') && 
                ($this->filteredInputArray['lastname'] === '') && 
                ($this->filteredInputArray['email1'] === '') && 
                ($this->filteredInputArray['email2'] === '') && 
                ($this->filteredInputArray['captcha'] === ''))
        {   
            $mandatoryFieldsArray = ['firstname', 'lastname', 'email1', 'email2', 'captcha'];

            foreach($mandatoryFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = 'You must fill this in.' ;
                $this->testResultsArray[$value]   = false;
                
                //Free up resources.
                $this->validationMetaArray[$value]       = NULL;
                $this->phpFieldValidatationArray[$value] = NULL;
                $this->phpFieldErrMsgsArray[$value]      = NULL;
                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
            }

            $this->validationMetaArray     = NULL;
            $this->phpFieldValidationArray = NULL;
            $this->phpFieldErrMsgsArray    = NULL;
            $mandatoryFieldsArray          = NULL;
            unset($this->validationMetaArray, $this->phpFieldValidationArray, $this->phpFieldErrMsgsArray, $mandatoryFieldsArray);
            return true;
        }
        
        return false;
    }
    
    protected function translateValidatedInput()  //Translate form inputs into database data.
    {
        $this->translatedInputArray = $this->filteredInputArray; 
        $firstname = $this->translatedInputArray['firstname'];
        $lastname = $this->translatedInputArray['lastname'];

        //Add essential elements.
        $this->translatedInputArray['subject'] = 'New Subscription! (The Fortress Newsletter)';
        $this->translatedInputArray['message'] = "{$firstname} {$lastname} has subscribed to The Fortress Newsletter from the newsletter webpage!";

        //Remove unnecessary data from the translated array.
        $this->translatedInputArray['newsSubBtn'] = NULL;
        $this->translatedInputArray['email2']     = NULL;
        $firstname = NULL;
        $lastname  = NULL;

        unset($this->translatedInputArray['newsSubBtn'], $this->translatedInputArray['email2'], $firstname, $lastname);
        return;
    }

    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = ($this->names + $this->emails + $this->filteredInputArray);
        return;
    }
    
    protected function myValidator()            //The wrapper for the task of validating form inputs.
    {
        //---------FORM TOKEN VALIDATION---------------
        $this->tokens = $this->extractFilteredElements($this->validatorTargets['formTokenValidator']);
        
        //Validate "token".
        $this->formTokenValidator->setFilteredInputArray($this->tokens);
        
        //Free up resources.
        $this->tokens = NULL;
        unset($this->tokens);
        
        $this->formTokenValidator->validate();
        
        //----------CAPTCHA VALIDATION-----------------
        $this->captchas = $this->extractFilteredElements($this->validatorTargets['captchaValidator']);

        //Validate "captcha".
        $this->captchaValidator->setFilteredInputArray($this->captchas);
        
        //Free up resources.
        $this->captchas = NULL;
        unset($this->captchas);
        
        if(!$this->captchaValidator->validate())
        {
            return false;
        }
        //--------------------------------------------
        
        if($this->isVoidInput())  //Blank form submission test.
        {
            return false;
        }
        
        //Prune $this->filteredInputArray. Copy appropriate elements into new arrays.
        $this->names  = $this->extractFilteredElements($this->validatorTargets['nameValidator']);
        $this->emails = $this->extractFilteredElements($this->validatorTargets['emailValidator']);

        //Use PHP FILTER functions to validate input.
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);
        
        //Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);

        //Free up resources.
        $this->phpFieldErrMsgsArray = NULL;
        $phpFilterResults           = NULL;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);

        /*******************Use personsal validation methods.******************/
        
        //Validate everything else using "variable" functions / methods.
        $this->coreValidatorLogic();
        
        //Free up resources.
        $this->validationMetaArray = NULL;
        unset($this->validationMetaArray);
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

        /**********************************************************************/

        //Merge the names, emails, and other arrays together again.
        $this->mergeFilteredInputArrays();

        if(!in_array(false, $this->testResultsArray, true))
        {
            $this->translateValidatedInput();
            return true;
        }
        /**********************************************************************/


        return false;
    }
    
    /*Unique Field Validator Functions*/
    protected function newsSubBtn($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            return true;
        }
        
        return false;
    }
}
?>