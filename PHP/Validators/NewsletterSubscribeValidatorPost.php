<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class NewsletterSubscribeValidatorPost extends Validator
{
    /* Properties */

    //Arrays
    private $tokens = NULL;
    private $emails = NULL;
    
    //Objects
    private $formTokenValidator = NULL;
    private $emailValidator = NULL;
    
    /* Blacklisting Regular Expressions */
    private $newsSubRegex = '/(?>[^\p{C}\p{M}\p{N}\p{P}\p{S}\p{Z}0-9;\' %-])/u';
    
    /* Constructor */
    public function __construct(Validator $formTokenValidator, Validator $emailValidator, array $filteredInput, array $transitoryInputs = NULL)
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
                                     'options' => ['regexp' => $this->newsSubRegex]]
                  ];

        $phpFEMA = [
                        'ajaxFlag'   => 'Illegal AJAX flag!',
                        'nocache'    => 'Illegal nocache value!',
                        'newsSubBtn' => 'Illegal nocache value!',
                   ];
        
        $validationMA = [
                            'ajaxFlag'   => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => '1', 'rangeOfValues' => NULL],
                            'nocache'    => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1000000, 'pattern' => '/(?>\A[0-9]{1,7}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL],
                            'newsSubBtn' => ['kind' => 'word', 'type' => 'string', 'min' => 9, 'max' => 9, 'pattern' => '/(?>\A(?:Subscribe){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  'Subscribe', 'rangeOfValues' => NULL]
                        ];
        
        $validatorTargets = [
                                'formTokenValidator' => ['token'],
                                'emailValidator'     => ['email']
                            ];
 
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInput, $validatorTargets, $transitoryInputs);
        $this->formTokenValidator = $formTokenValidator;
        $this->emailValidator = $emailValidator;
    }
    
    
    protected function isVoidInput()              //Test for blank form submission.
    {
        if(($this->filteredInputArray['email'] === ''))
        {   
            $mandatoryFieldsArray = ['email'];

            foreach($mandatoryFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = 'The control submitted an invalid value.' ;
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
        $this->translatedInputArray['newsSubBtn'] = NULL;
        unset($this->translatedInputArray['newsSubBtn']);
        return;
    }

    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = array_merge($this->emails, $this->filteredInputArray);
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
        
        $this->emails = $this->extractFilteredElements($this->validatorTargets['emailValidator']);

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
        
        //Validate "email".
        $this->emailValidator->setFilteredInputArray($this->emails);
        $this->emailValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->emailValidator, $this->emails);
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
}
