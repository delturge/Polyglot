<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class AjaxValidator extends Validator
{
    /* Properties */

    //Blacklisting Regular Expression.
    private $nameRegex    = '/(?>[^\p{C}\p{N}\p{S}0-9;_%])/u';
    
    /* Constructor */
    public function __construct()
    {
        $phpFEMA = ['ajaxFlag' => 'Illegal AJAX flag!'];
        $validationMA = ['ajaxFlag' => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => '1', 'rangeOfValues' => NULL]];
        parent::__construct([], $phpFEMA, $validationMA, []);
    }
    
    /* Validators*/
    
    /* Mutators */
    private function setIndexedPHPFilterInstructions()
    {        
        for($i = 0, $length = count($this->filteredInputArray); $i <= $length; ++$i)
        {
            $index = " {$i}";
            $this->phpFieldValidationArray[$index] = ['filter'  => FILTER_VALIDATE_INT,
                                                      'flags'   => FILTER_REQUIRE_SCALAR,
                                                      'options' => ['min_range' => 1, 'max_range' => 1]];
        }

        return;
    }
    
    private function setNamedPHPFilterInstructions()
    {   
        foreach(array_keys($this->filteredInputArray) as $key)
        {
            $this->phpFieldValidationArray[$key] = ['filter'  => FILTER_VALIDATE_INT,
                                                    'flags'   => FILTER_REQUIRE_SCALAR,
                                                    'options' => ['min_range' => 1, 'max_range' => 1]];
        }
        
        return;
    }

    public function setFilteredInputArray(array $emailAddresses)
    {
        $this->filteredInputArray = $this->isArrayOfStrings($emailAddresses);
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
        foreach($this->filteredInputArray as $key => $name)
        {
            $this->testResultsArray[$key] = $this->ajaxFlag($name, $this->validationMetaArray['ajaxFlag'], $this->errorMessagesArray[$key]);
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
        
        $this->coreValidatorLogic();            //Usee $this->mail() to validate e-mail addresses.

        /**********************************************************************/
        
        if(!in_array(false, $this->testResultsArray, true))
        {
            $this->translateValidatedInput(); 

            //Free up resources.
            $this->filteredInputArray = NULL;
            unset($this->filteredInputArray);
            return true; 
        }
        
        return false;
    }
    
    private function ajaxFlag($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            return true;
        }
        
        return false;
    }
}
?>