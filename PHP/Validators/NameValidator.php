<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class NameValidator extends Validator
{
    /* Properties */

    //Blacklisting Regular Expression.
    private $nameRegex = '/(?>[^\p{C}\p{N}\p{S}0-9;_%])/u';
    
    /* Constructor */
    public function __construct()
    {
        $phpFEMA = [
                        'firstname'  => 'Illegal characters. Try again.',
                        'middlename' => 'Illegal characters. Try again.',
                        'maidenname' => 'Illegal characters. Try again.',
                        'lastname'   => 'Illegal characters. Try again.'
                   ];
        
        $validationMA = ['name' => ['kind' => 'name', 'type' => 'string', 'min' => 1, 'max' => 30, 'pattern' => '/(?>\A[A-Z]{1}?[A-Za-z\' -]{0,29}\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL]];

        parent::__construct([], $phpFEMA, $validationMA, []);
    }
    
    /* Validators*/
    
    /* Mutators */
    public function setIndexedPHPFilterInstructions()
    {        
        for($i = 0, $length = count($this->filteredInputArray); $i <= $length; ++$i)
        {
            $index = " {$i}";
            $this->phpFieldValidationArray[$index] = ['filter'  => FILTER_VALIDATE_REGEXP, 
                                                      'flags'   => FILTER_REQUIRE_SCALAR,
                                                      'options' => ['regexp' => $this->nameRegex]];
        }

        return;
    }
    
    public function setNamedPHPFilterInstructions()
    {
        foreach(array_keys($this->filteredInputArray) as $key)
        {
            $this->phpFieldValidationArray[$key] = ['filter'  => FILTER_VALIDATE_REGEXP, 
                                                    'flags'   => FILTER_REQUIRE_SCALAR, 
                                                    'options' => ['regexp' => $this->nameRegex]];
        }

        return;
    }

    public function setFilteredInputArray(array $emailAddresses)
    {
        $this->filteredInputArray = $emailAddresses;
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
            $this->testResultsArray[$key] = $this->name($name, $this->validationMetaArray['name'], $this->errorMessagesArray[$key]);
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
    
    private function name($string, array $validationMetaArray, &$errorMessage)
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