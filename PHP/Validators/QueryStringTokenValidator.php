<?php
namespace Isaac\Validators;

require_once 'Validator.php';


class QueryStringTokenValidator extends Validator
{
    /* Properties */
    
    //Blacklisting regular expression.
    private $tokenPartRegex = '/(?>[^(\A\z)\p{C}\p{Lm}\p{Lo}\p{Lt}\p{M}\p{Nl}\p{No}\p{Pc}\p{Pe}\p{Pf}\p{Pi}\p{Ps}\p{Sk}\p{Sm}\p{So}\p{Z} %;\'-])/u';

    /* Constructor */
    public function __construct()
    {
        $phpFVA = [
                      'a' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->tokenPartRegex]],
                      'b' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->tokenPartRegex]],
                      'c' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->tokenPartRegex]],
                      'd' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->tokenPartRegex]],
                      'e' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->tokenPartRegex]],
                      'f' => ['filter'  => FILTER_VALIDATE_REGEXP,
                              'flags'   => FILTER_REQUIRE_SCALAR,
                              'options' => ['regexp' => $this->tokenPartRegex]]
                  ];
        
        $phpFEMA = [
                        'a' => 'Bad hash value!',
                        'b' => 'Bad hash value!',
                        'c' => 'Bad hash value!',
                        'd' => 'Bad hash value!',
                        'e' => 'Bad hash value!',
                        'f' => 'Bad hash value!'
                   ];
        
        $validationMA = ['tokenPart' => ['kind' => 'text', 'type' => 'string', 'min' => 10, 'max' => 10, 'pattern' => '/\A(?>[.$\/0-9A-Za-z]{10}?){1}?\z/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL]];
        $filteredInput = [];
        $validatorTargets = [];
        $transitoryInputs = [];
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInput, $validatorTargets, $transitoryInputs);
    }

    protected function setIndexedPHPFilterInstructions()
    {        
        for($i = 0, $length = count($this->filteredInputArray); $i <= $length; ++$i)
        {
            $index = " {$i}";
            $this->phpFieldValidationArray[$index] = ['filter'  => FILTER_VALIDATE_REGEXP, 
                                                      'flags'   => FILTER_REQUIRE_SCALAR,
                                                      'options' => ['regexp' => $this->tokenPartRegex]];
        }
        
        return;
    }
    
    protected function setNamedPHPFilterInstructions()
    {
        foreach(array_keys($this->filteredInputArray) as $key)
        {
            $this->phpFieldValidationArray[$key] = ['filter'  => FILTER_VALIDATE_REGEXP, 
                                                    'flags'   => FILTER_REQUIRE_SCALAR, 
                                                    'options' => ['regexp' => $this->tokenPartRegex]];
        }
        
        return;
    }

    public function setFilteredInputArray(array $queryStringArray)
    {
        $this->filteredInputArray = $queryStringArray;
        $this->programValidator();
    }
    
    protected function isVoidInput()
    {
        return;
    }
    
    protected function translateValidatedInput()
    {
        $this->translatedInputArray = $this->filteredInputArray;
        return;
    }
    
    protected function coreValidatorLogic() 
    {
        foreach($this->filteredInputArray as $key => $tokenPart)
        {
            if($this->testResultsArray[$key] === true) //Only check the ones that passed the PHP Filter validation.
            {
                $this->testResultsArray[$key] = $this->tokenPart($tokenPart, $this->validationMetaArray['tokenPart'], $this->errorMessagesArray[$key]);
            }
        }
        
        $this->validationMetaArray = NULL;
        unset($this->validationMetaArray);
        return;
    }
    
    protected function myValidator()
    {
        //$this->isVoidInput();    //Checks for empty strings.

        /*******************Use PHP validation functions.**********************/
        
        //Use PHP FILTER functions to validate input.
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);
        
        //Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);
        
        //Free up resources.
        $this->phpFieldErrMsgsArray = NULL;
        $phpFilterResults           = NULL;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);
        
        /*******************Use personal validation methods.*******************/
        
        $this->coreValidatorLogic();

        /**********************************************************************/
        
        if(!in_array(false, $this->testResultsArray, true))
        {
            $this->translateValidatedInput(); 

            //Free up resources.
            $this->filteredInputArray = NULL;
            unset($this->filteredInputArray);
            return true; 
        }
        
        error_log("QueryStringTokenValidator Error Messages\n" . print_r($this->errorMessagesArray,true));
        return false;
    }
    
    private function tokenPart($string, array $validationMetaArray, &$errorMessage)  //A token that does not validate is a serious situation.
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }
}
?>