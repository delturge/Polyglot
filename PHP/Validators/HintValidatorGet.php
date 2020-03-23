<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class HintValidatorGet extends Validator
{    
    /* Properties */
    
    //Arrays
    protected $tokenParts = NULL;
    
    //Objects
    private $queryStringTokenValidator = NULL;
    
    /* Constructor */
    public function __construct(Validator $queryStringTokenValidator, array $filteredInput, $transitoryInputs = NULL)
    {
        $phpFVA = [];
        $phpFEMA = [];
        $validationMA = [];
        $validatorTargets = ['queryStringTokenValidator' => [' 1', ' 2', ' 3', ' 4', ' 5', ' 6']];
        
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInput, $validatorTargets, $transitoryInputs);
        $this->queryStringTokenValidator = $queryStringTokenValidator;
    }

    protected function isVoidInput()
    {
        if(!isset($this->filteredInputArray[' 1'],
                  $this->filteredInputArray[' 2'],
                  $this->filteredInputArray[' 3'], 
                  $this->filteredInputArray[' 4'],
                  $this->filteredInputArray[' 5'],
                  $this->filteredInputArray[' 6']))
        {
            return true;
        }
        
        return false;
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

        return false;
    }
}
?>