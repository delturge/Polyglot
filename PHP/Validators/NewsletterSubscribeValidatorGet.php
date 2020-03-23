<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class NewsletterSubscribeValidatorGet extends Validator
{    
    /* Properties */
    
    //Arrays
    protected $tokenParts = NULL;
    
    //Objects
    private $cipher = NULL;
    private $queryStringTokenValidator = NULL;
    
    //Blacklisting Regular Expressions
    private $nonBase64Regex = '/(?>[^(\A\z)\p{C}\p{Lm}\p{Lo}\p{Lt}\p{M}\p{Nl}\p{No}\p{P}\p{Sc}\p{Sk}\p{So}\p{Z};\' %-]){1}?/u';
    
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
                        'a' => 'Illegal base64 characters!',
                        'b' => 'Illegal base64 characters!',
                        'c' => 'Illegal base64 characters!',
                        'd' => 'Illegal base64 characters!',
                        'e' => 'Illegal base64 characters!',
                        'd' => 'Illegal base64 characters!'
                   ];
        
        $validationMA = ['base64Token' => ['kind' => 'base64', 'type' => 'string', 'min' => 96, 'max' => 96, 'pattern' => '/(?>\A[+\/0-9=A-Za-z]{96}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL]];
        
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
    
    protected function translateValidatedInput()
    {
        $this->translatedInputArray = $this->filteredInputArray;
        return;
    }
     
    /* Protected Methods */
    protected function myValidator()
    {
        if($this->isVoidInput())  //Blank form submission test.
        {
            return false;
        }
        
        
        
        
        
        //Prune $this->filteredInputArray. Copy appropriate element into new array.
        $this->tokenParts = $this->extractFilteredElements($this->validatorTargets['queryStringTokenValidator']);
        
        //Validate "token parts".
        $this->queryStringTokenValidator->setFilteredInputArray($this->tokenParts);
        $this->queryStringTokenValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->queryStringTokenValidator, $this->tokenParts);
        //--------------------------------------
                
        if(!in_array(false, $this->testResultsArray, true))
        {
            $this->translateValidatedInput();
            $this->filteredInputArray = NULL;
            unset($this->filteredInputArray);
            return true;
        }

        error_log(print_r($this->errorMessagesArray,true));
        return false;
    }
    
    /* Public Methods */
}
?>