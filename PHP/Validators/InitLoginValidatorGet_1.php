<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class InitLoginValidatorGet extends Validator
{
    /* Properties */
    
    //Arrays
    protected $tokenParts = NULL;
    
    //Objects
    private $cipher = NULL;
    private $queryStringTokenValidator = NULL;
    
    //Blacklisting Regular Expressions
    private $nonBase64Regex = '/(?>[^(\A\z)\p{C}\p{Lm}\p{Lo}\p{Lt}\p{Mc}\p{Me}\p{Nl}\p{No}\p{Pc}\p{Pe}\p{Pf}\p{Pi}\p{Ps}\p{S}\p{Z};\' %])/u';
    
    /* Constructor */
    public function __construct(Validator $queryStringTokenValidator, Cipher $cipher, array $filteredInput, $transitoryInputs = NULL)
    {
        $phpFVA = [
                      'a' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->nonBase64Regex]],
                      'b' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->nonBase64Regex]],
                      'c' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->nonBase64Regex]],
                      'd' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->nonBase64Regex]],
                      'e' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->nonBase64Regex]],
                      'f' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->nonBase64Regex]]
                  ];
                
        $phpFEMA = [
                        'a' => 'Illegal url safe base64 characters!',
                        'b' => 'Illegal url safe base64 characters!',
                        'c' => 'Illegal url safe base64 characters!',
                        'd' => 'Illegal url safe base64 characters!',
                        'e' => 'Illegal url safe base64 characters!',
                        'f' => 'Illegal url safe base64 characters!'
                   ];
        
        $validationMA = ['base64Token' => ['kind' => 'base64', 'type' => 'string', 'min' => 160, 'max' => 160, 'pattern' => '/(?>\A[\-.0-9A-Z_a-z]{160}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL]];
        
        $validatorTargets = ['queryStringTokenValidator' => ['a', 'b', 'c', 'd', 'e', 'f']];
        
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInput, $validatorTargets, $transitoryInputs);
        $this->queryStringTokenValidator = $queryStringTokenValidator;
        $this->cipher = $cipher;
    }

    protected function isVoidInput()
    {
        return (!isset($this->filteredInputArray['a'],
                  $this->filteredInputArray['b'],
                  $this->filteredInputArray['c'], 
                  $this->filteredInputArray['d'],
                  $this->filteredInputArray['e'],
                  $this->filteredInputArray['f']));
    }
     
    /**
     * The overriding, core logic for the core validation logic.
     */
    protected function coreValidatorLogic()
    {
        foreach($this->filteredInputArray as $key => $urlSafeBase64Token)
        {
            if($this->testResultsArray[$key] === true) //Only check the ones that passed the PHP Filter validation.
            {
                $this->testResultsArray[$key] = $this->base64Token($urlSafeBase64Token, $this->validationMetaArray['base64Token'], $this->errorMessagesArray[$key]);
            }
        }

        return;
    }
    
    protected function translateValidatedInput()
    {
        $this->translatedInputArray = $this->filteredInputArray;
        return;
    }

    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = $this->tokenParts;
    }
    
    protected function myValidator()
    {
        if($this->isVoidInput())  //Blank form submission test.
        {
            return false;
        }
        
        /************ Use PHP FILTER functions to test 32 character, base64 query string tokens.**************/
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);
        
        //Check and interpret PHP FILTER validation results (32 characters, base64).
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);

        //Free up resources.
        $this->phpFieldErrMsgsArray = NULL;
        $phpFilterResults           = NULL;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);
        /**********************************************************************/
        
        if(in_array(false, $this->testResultsArray, true))  //The first batch of test (PHP Filter) must not fail.
        {
            $this->filteredInputArray  = NULL;
            $this->validationMetaArray = NULL;
            $this->validatorTargets    = NULL;
            unset($this->filteredInputArray, $this->validationMetaArray, $this->validatorTargets);
            error_log("Error Messages\n\n" . print_r($this->errorMessagesArray,true));
            return false;
        }

        /*******************Use personsal validation methods.******************/
        $this->coreValidatorLogic(); //Validate the 96 character, base64 query string tokens.
        
        //Free up resources.
        $this->validationMetaArray = NULL;
        unset($this->validationMetaArray);

        if(in_array(false, $this->testResultsArray, true))  //The second batch of test (PHP Filter) must not fail.
        {
            $this->filteredInputArray  = NULL;
            $this->validatorTargets    = NULL;
            unset($this->filteredInputArray, $this->validatorTargets);
            error_log("Error Messages\n\n" . print_r($this->errorMessagesArray,true));
            return false;
        }
        //--------------------------------------
        
        error_log("Encrypted and URL safe Base64 encoded. \n" . print_r($this->filteredInputArray,true));
        
        //Decrypt and copy appropriate elements into a new array.
        $this->filteredInputArray = $this->cipher->decryptQueryStringArray($this->filteredInputArray);
        
        error_log("Base64 deocded and decrypted. \n" . print_r($this->filteredInputArray,true));
        
        $this->tokenParts = $this->extractFilteredElements($this->validatorTargets['queryStringTokenValidator']);
        
        //Validate 10 character, Blowfish Hash Encrypted, token parts".
        $this->queryStringTokenValidator->setFilteredInputArray($this->tokenParts);
        $this->queryStringTokenValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->queryStringTokenValidator, $this->tokenParts);
        //--------------------------------------
        
        $this->mergeFilteredInputArrays();
        
        if(!in_array(false, $this->testResultsArray, true))
        {
            $this->translateValidatedInput();
            $this->filteredInputArray = NULL;
            unset($this->filteredInputArray);
            return true;
        }

        error_log("InitLoginValidatorGET Error Messages\n" . print_r($this->errorMessagesArray,true));
        return false;
    }
    
    /*Unique Field Validator Functions*/
    protected function base64Token($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }
}
?>