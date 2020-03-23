<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class InitLoginValidatorPost extends Validator
{    
    /* Properties */

    //Arrays
    private $tokens    = NULL;
    private $usernames = NULL;
    private $passwords = NULL;
    
    //Objects
    private $formTokenValidator = NULL;
    private $emailValidator    = NULL;
    private $passwordValidator = NULL;
    
    //Blacklisting Regular Expressions
    private $initLoginRegex = '/(?>[^\p{C}\p{M}\p{N}\p{P}\p{S}\p{Z}0-9;\' %-])/u';
    
    public function __construct(Validator $formTokenValidator, Validator $emailValidator, Validator $passwordValidator, array $filteredInput, array $transitoryInputs = NULL)
    {
        $phpFVA = [
                      'ajaxFlag'     => ['filter'  => FILTER_VALIDATE_INT,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['min_range' => 1, 'max_range' => 1]],
                      'nocache'      => ['filter'  => FILTER_VALIDATE_INT,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['min_range' => 0, 'max_range' => 1000000]],
                      'initLoginBtn' => ['filter'  => FILTER_VALIDATE_REGEXP,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['regexp' => $this->initLoginRegex]]
                  ];
        
        $phpFEMA = [
                        'ajaxFlag'     => 'Illegal AJAX flag!',
                        'nocache'      => 'Illegal nocache value!',
                        'initLoginBtn' => 'Bad button entry. Try again.'
                   ];
        
        $validationMA = [
                            'ajaxFlag'     => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => '1', 'rangeOfValues' => NULL],
                            'nocache'      => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1000000, 'pattern' => '/(?>\A[0-9]{1,7}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL],
                            'initLoginBtn' => ['kind' => 'word', 'type' => 'string', 'min' => 5, 'max' => 5, 'pattern' => '/(?>\A(?:Login){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 'Login', 'rangeOfValues' => NULL]
                        ];
        
        $validatorTargets = [
                                'formTokenValidator' => ['token'],
                                'emailValidator'     => ['username'],
                                'passwordValidator'  => ['password']
                            ];
        
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInput, $validatorTargets, $transitoryInputs);
        $this->formTokenValidator = $formTokenValidator;
        $this->emailValidator     = $emailValidator;
        $this->passwordValidator  = $passwordValidator;
    }
    
    /* Accessors */    
    protected function isVoidInput()              //Test for blank form submission.
    {
        if(($this->filteredInputArray['username'] === '') && 
                ($this->filteredInputArray['password'] === ''))
        {   
            $mandatoryFieldsArray = ['username', 'password'];

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

    protected function translateValidatedInput()  //Translate form inputs into database data.
    {
        $this->translatedInputArray = $this->filteredInputArray;
        $this->translatedInputArray['initLoginBtn'] = NULL;
        unset($this->translatedInputArray['initLoginBtn']);
        return;
    }
    
    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = array_merge($this->usernames, $this->filteredInputArray);
    }

    protected function myValidator()              //The wrapper for the task of validating form inputs.
    {
        //---------FORM TOKEN VALIDATION---------------
        $this->tokens = $this->extractFilteredElements($this->validatorTargets['formTokenValidator']);
        
        //Validate "token".
        $this->formTokenValidator->setFilteredInputArray($this->tokens);
        
        //Free up resources.
        $this->tokens = NULL;
        unset($this->tokens);
        
        $this->formTokenValidator->validate();
        //--------------------------------------------
        
        if($this->isVoidInput())  //Blank form submission test.
        {
            return false;
        }

        $this->usernames = $this->extractFilteredElements($this->validatorTargets['emailValidator']);
        $this->passwords = $this->extractFilteredElements($this->validatorTargets['passwordValidator']);

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
        
        //This wrapper method calls "variable functions" that validate each field.
        $this->coreValidatorLogic();
        
        //Free up resources.
        $this->validationMetaArray = NULL;
        unset($this->validationMetaArray);
        //----------------------------------------------------------------------
        
        //Validate "username".
        $this->emailValidator->setFilteredInputArray($this->usernames);
        $this->emailValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->emailValidator, $this->usernames);
        //--------------------------------------
        
        //Validate "password".
        $this->passwordValidator->setFilteredInputArray($this->passwords);
        $this->passwordValidator->validate();        
        $this->mergeValidatorTestResultsAndMessages($this->passwordValidator, $this->passwords);
        //--------------------------------------
        
        //Merge the names, emails, and other arrays together again.
        $this->mergeFilteredInputArrays();
        
        /**********************************************************************/

        if(!in_array(false, $this->testResultsArray, true))
        {
            $this->translateValidatedInput();
            return true;
        }

        return false;
    }
    
    //Field validators
    protected function initLoginBtn($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validate_input($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            return true;
        }
        return false;
    }
    
    protected function username($string, array $validationMetaArray, &$errorMessage)  //This is an override.
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validate_input($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            return true;
        }
        
        throw new SecurityException('The hidden username field on the initial login form has been tampered with.');
    }
}
?>