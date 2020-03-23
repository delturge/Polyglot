<?php
declare(strict_types=1);

namespace Isaac\Validators;

final class FormTokenValidator extends Validator
{
    /* Properties */

    //Blacklisting Regular Expression.
    private $tokenRegex = '/(?>[^\p{C}\p{P}\p{S}\p{Z}g-z;\' %-])/u';
    
    /* Constructor */
    public function __construct()
    {
        $phpFVA = [
            'token' => [
                'filter'  => FILTER_VALIDATE_REGEXP,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => ['regexp' => $this->tokenRegex]
            ]
        ];
              
        $phpFEMA      = ['token' => 'Illegal form token characters.'];
        $validationMA = ['token' => ['optional' => false, 'kind' => 'digest', 'type' => 'string', 'min' => 32, 'max' => 32, 'pattern' => '/(?>\A[a-f0-9]{32}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  $_SESSION['token'], 'rangeOfValues' => null]];
        parent::__construct($phpFVA, $phpFEMA, $validationMA, []);
    }
    
    /* Validators*/
    
    /* Mutators */    
//    private function setIndexedPHPFilterInstructions()
//    {        
//        for($i = 0, $length = count($this->filteredInputArray); $i <= $length; ++$i)
//        {
//            $index = " {$i}";
//            $this->phpFieldValidationArray[$index] = ['filter'  => FILTER_VALIDATE_REGEXP, 
//                                                      'flags'   => FILTER_REQUIRE_SCALAR,
//                                                      'options' => ['regexp' => $this->tokenRegex]];
//        }
//
//        return;
//    }
//    
//    private function setNamedPHPFilterInstructions()
//    {   
//        foreach(array_keys($this->filteredInputArray) as $key)
//        {
//            $this->phpFieldValidationArray[$key] = ['filter'  => FILTER_VALIDATE_REGEXP, 
//                                                    'flags'   => FILTER_REQUIRE_SCALAR, 
//                                                    'options' => ['regexp' => $this->tokenRegex]];
//        }
//        
//        return;
//    }
//
//    public function setFilteredInputArray(array $emailAddresses)
//    {
//        $this->filteredInputArray = $this->isArrayOfStrings($emailAddresses);
//        $this->programValidator();
//    }
    
    protected function isVoidInput()
    {
        return;
    }
    
    protected function translateValidatedInput()
    {
        $this->cleanData = $this->filteredData;
    }
    
    protected function coreValidatorLogic()
    {
        if ($this->testResultsArray['token'] === true) {
            $this->testResultsArray['token'] = $this->token($this->filteredInputArray['token'], $this->validationMetaArray['token'], $this->errorMessagesArray['token']);
        }

        $this->validationMetaArray = null;
        unset($this->validationMetaArray);
    }
    
    protected function altCoreValidatorLogic()
    {
        if ($this->testResultsArray['loginToken'] === true) {
            $this->testResultsArray['loginToken'] = $this->token($this->filteredInputArray['loginToken'], $this->validationMetaArray['token'], $this->errorMessagesArray['loginToken']);
        }

        $this->validationMetaArray = null;
        unset($this->validationMetaArray);
    }
    
    protected function test()
    {       
        //$this->isVoidInput();    //Checks for empty strings.

        /*******************Use PHP validation functions.**********************/
        
        //Use PHP FILTER functions to validate input.
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);
        
        //Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);
        
        //Free up resources.
        $this->phpFieldErrMsgsArray = null;
        $phpFilterResults           = null;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);
        
        /*******************Use personal validation methods.*******************/

        (isset($this->filteredInputArray['token'])) ? $this->coreValidatorLogic() : $this->altCoreValidatorLogic();

        /**********************************************************************/
        
        if (!in_array(false, $this->testResultsArray, true)) {
            $this->translateValidatedInput(); 

            // Free up resources.
            $this->filteredData = null;
            unset($this->filteredData);
            return true; 
        }
        
        error_log(print_r($this->errorMessagesArray,true));
        throw new SecurityException('Illegal form token submitted. Unknown form data source.');
    }
    
    private function token(string $value, array $validationMetaArray, &$errorMessage)  //A token that does not validate is a serious situation.
    {
        if (($this->testResults['token'] !== true) || ($this->testResults['loginToken'] !== true)) {
            return false;
        }
        
        return $this->validateInput($value, $optional, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }
}
?>