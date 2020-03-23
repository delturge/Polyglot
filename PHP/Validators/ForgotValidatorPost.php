<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class ForgotValidatorPost extends Validator
{
    /* Properties */

    //Arrays
    private $tokens    = NULL;
    private $captchas  = NULL;
    private $usernames = NULL;
    
    //Objects
    private $formTokenValidator = NULL;
    private $captchaValidator   = NULL;
    private $emailValidator     = NULL;
    
    //Blacklisting Regular Expressions
    private $forgotBtnRegex = '/(?>[^(\A\z)\p{C}\p{M}\p{N}\p{P}\p{S}\p{Z}0-9;\' %-])/u';
    
    public function __construct(Validator $formTokenValidator, Validator $captchaValidator, Validator $emailValidator, array $filteredInput, array $transitoryInputs = NULL)
    {        
        $phpFVA = [
                    'ajaxFlag'          => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 1, 'max_range' => 1]],
                    'nocache'           => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 0, 'max_range' => 1000000]],
                    'forgotPasswordBtn' => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->forgotBtnRegex]]
                  ];
        
        $phpFEMA = [
                        'ajaxFlag'          => 'Illegal AJAX flag!',
                        'nocache'           => 'Illegal nocache value!',
                        'forgotPasswordBtn' => 'Bad button entry. Try again.'
                   ];
        
        $validationMA = [
                            'ajaxFlag'          => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 1, 'rangeOfValues' => NULL],
                            'nocache'           => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1000000, 'pattern' => '/(?>\A[0-9]{1,7}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL],
                            'forgotPasswordBtn' => ['kind' => 'word', 'type' => 'string', 'min' => 6, 'max' => 6, 'pattern' => '/(?>\A(?:Submit){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 'Submit', 'rangeOfValues' => NULL]
                        ];

        $validatorTargets = [
                                'formTokenValidator' => ['token'],
                                'captchaValidator'   => ['captcha'],
                                'emailValidator'     => ['username']
                            ];
        
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInput, $validatorTargets, $transitoryInputs);
        $this->formTokenValidator = $formTokenValidator;
        $this->captchaValidator   = $captchaValidator;
        $this->emailValidator     = $emailValidator;
    }
    
    protected function isVoidInput()              //Test for blank form submission.
    {
        if(($this->filteredInputArray['username'] === '') && ($this->filteredInputArray['captcha'] === ''))
        {   
            $mandatoryFieldsArray = ['username', 'captcha'];

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
            
            //Free up resources.
            $this->validationMetaArray     = NULL;
            $this->phpFieldValidationArray = NULL;
            $this->phpFieldErrMsgsArray    = NULL;
            $mandatoryFieldsArray          = NULL;
            unset($this->validationMetaArray, $this->phpFieldValidationArray, $this->phpFieldValidationArray, $mandatoryFieldsArray);
            return true;
        }
        
        return false;
    }
    
    protected function translateValidatedInput()    //Translate form inputs into database data.
    {             
        $this->translatedInputArray = $this->filteredInputArray;
        
        //Remove unnecessary data from the translated array.
        $this->translatedInputArray['forgotPasswordBtn'] = NULL;
        unset($this->translatedInputArray['forgotPasswordBtn']);
        
        return;
    }

    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = array_merge($this->usernames, $this->filteredInputArray);
    }
    
    protected function myValidator() //The wrapper for the task of validating form inputs.
    {        
        /**************** Copy appropriate elements into new arrays. **********/

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
        
        //Prune $this->filteredInputArray. Copy appropriate element into new array.
        $this->usernames = $this->extractFilteredElements($this->validatorTargets['emailValidator']);

        /************************* USE PHP FILTER *****************************/
        
        //Use PHP FILTER functions to validate input.
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);
        
        //Free up resources.
        $this->phpFieldValidatationArray = NULL;
        unset($this->phpFieldValidatationArray);
        
        //Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);
        
        //Free up resources.
        $this->phpFieldErrMsgsArray = NULL;
        $phpFilterResults           = NULL;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);
       
        /*******************Use peronsal validation methods.*******************/
        
        $this->coreValidatorLogic();     //Calls "variable functions" that validate each field.
        
        //Free up resources.
        $this->validationMetaArray = NULL;
        unset($this->validationMetaArray);
        //--------------------------------------
        
        //Validate "username".
        $this->emailValidator->setFilteredInputArray($this->usernames);
        $this->emailValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->emailValidator, $this->usernames);
        //--------------------------------------
        
        //Merge the usernames and other arrays together again.
        $this->mergeFilteredInputArrays();

        /**********************************************************************/

        if(!in_array(false, $this->testResultsArray, true))
        {
            $this->translateValidatedInput();
            return true;
        }

        return false;
    }
    
    /*Unique Field Validator Functions*/
    protected function forgotPasswordBtn($string, array $validationMetaArray, &$errorMessage)
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