<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class ChangePasswordValidatorPost extends Validator
{
    /* Properties */
    
    //Arrays
    private $tokens    = NULL;
    private $passwords = NULL;
    
    //Objects
    private $formTokenValidator = NULL;
    private $passwordValidator = NULL;
    
    //Blacklisting Regular Expressions
    private $changePasswordRegex = '/(?>[^\p{C}\p{M}\p{N}\p{P}\p{S}\p{Z}0-9;\' %-])/u';
    private $pwHintRegex         = '/(?>[^\p{C}\p{P}\p{S}\p{Z};\' %])/u';
    
    public function __construct(Validator $formTokenValidator, Validator $passwordValidator, array $filteredInput, array $transitoryInputs = NULL)
    {
        $phpFVA = [
                    'ajaxFlag'          => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 1, 'max_range' => 1]],
                    'nocache'           => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 0, 'max_range' => 1000000]],
                    'changePasswordBtn' => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->changePasswordRegex]],
                    'pw_hint_question'  => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 1, 'max_range' => 9]],
                    'pw_hint'           => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->pwHintRegex]]
                  ];
        
        $phpFEMA = [
                        'ajaxFlag'          => 'Illegal AJAX flag!',
                        'nocache'           => 'Illegal nocache value!',
                        'changePasswordBtn' => 'Illegal characters. Try again.',
                        'pw_hint_question'  => 'Bad selection Try again.',
                        'pw_hint'           => 'Illegal characters. Try again.'
                   ];
        
        $validationMA = [
                            'ajaxFlag'          => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => '1', 'rangeOfValues' => NULL],
                            'nocache'           => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1000000, 'pattern' => '/(?>\A[0-9]{1,7}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL],
                            'changePasswordBtn' => ['kind' => 'word', 'type' => 'string', 'min' => 5, 'max' => 5, 'pattern' => '/(?>\A(?:Change){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  'Change', 'rangeOfValues' => NULL],
                            'pw_hint_question'  => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 9, 'pattern' => '/\A(?>[1-9]{1}?){1}?\z/', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => [1, 2, 3, 4, 5, 6, 7, 8, 9]],
                            'pw_hint'           => ['kind' => 'text', 'type' => 'string', 'min' => 1, 'max' => 50, 'pattern' => '/\A(?:[A-Za-z0-9,.?!: -]{1,50}?){1}?\z/', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL]
                        ];
                
        $validatorTargets = [
                                'formTokenValidator' => ['token'],
                                'passwordValidator'  => ['password0', 'password1', 'password2']
                            ];
        
        parent::__construct($validationMA, $phpFVA, $phpFEMA, $filteredInput, $validatorTargets, $transitoryInputs);
        $this->formTokenValidator = $formTokenValidator;
        $this->passwordValidator = $passwordValidator;
    }
 
    /* Properties */
    protected $passwords = [];
    
    protected function isVoidInput()              //Test for blank form submission.
    {
        if(
            ($this->filteredInputArray['password0'] === '')        &&
            ($this->filteredInputArray['password1'] === '')        &&
            ($this->filteredInputArray['password2'] === '')        &&
            ($this->filteredInputArray['pw_hint_question'] === '') &&
            ($this->filteredInputArray['pw_hint_answer'] === '')
          )
        {   
            $mandatoryFieldsArray     = ['password0', 'password1', 'password2', 'pw_hint_answer'];
            $moreMandatoryFieldsArray = ['pw_hint_question'];

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
            
            foreach($moreMandatoryFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = 'You must make a selection.';
                $this->testResultsArray[$value]   = false;
                $this->filteredInputArray[$value] = '';
                
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
    
    private function passwordMatchTest(&$result1, &$result2, $password1, $password2, &$message1, &$message2)        //Compares two passwords.
    {
        if($this->isTrue($result1) && $this->isTrue($result2))
        {
            if($this->identical($password1, $password2))
            {
                return true;
            }
            else
            {
                $result1 = false;
                $result2 = false;
                $message1 = 'Does not match password below.';
                $message2 = 'Does not match password above.';
            }
        }

        return false;
    }

    protected function translateValidatedInput()    //Translate form inputs into database data.
    {
        $this->translatedInputArray = $this->filteredInputArray; 
        
        /**
         * Data translations.
         */
             
        //Remove unnecessary data from the translated array.
        $this->translatedInputArray['password0'] = NULL;
        $this->translatedInputArray['password2'] = NULL;
        $this->translatedInputArray['changePasswordBtn'] = NULL;

        unset(
                $this->translatedInputArray['password0'],
                $this->translatedInputArray['password2'],
                $this->translatedInputArray['changePasswordBtn']
              );

        return;
    }
    
    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = array_merge($this->passwords, $this->filteredInputArray);
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
        
        //Prune $this->filteredInputArray. Copy appropriate element into new array.
        $this->passwords = $this->extractFilteredElements($this->validatorTargets['passwordValidator']);

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
        
        //This wrapper method calls "variable functions" that validate each field.
        $this->coreValidatorLogic();
        
        //Free up resources.
        $this->validationMetaArray = NULL;
        unset($this->validationMetaArray);
        //--------------------------------------
        
        //Validate "password".
        $this->passwordValidator->setFilteredInputArray($this->passwords);
        $this->passwordValidator->validate();        
        $this->mergeValidatorTestResultsAndMessages($this->passwordValidator, $this->passwords);
        //--------------------------------------

        //Comparison of two passwords.
        $this->passwordMatchTest($this->testResultsArray['password1'], $this->testResultsArray['password2'],
                                                $this->emails['password1'], $this->emails['password2'],
                                                $this->errorMessagesArray['password1'], $this->errorMessagesArray['password2']);


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
 
    /*Unique Field Validator Functions*/
    protected function pwHintQuestion($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validate_input($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            return true;
        }
        
        return false;
    }
    
    protected function pwHintAnswer($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validate_input($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            return true;
        }
        
        return false;
    }
}
?>