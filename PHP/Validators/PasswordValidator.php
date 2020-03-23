<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class PasswordValidator extends Validator
{
    /* Properties */
    private $passwordRegex = '/(?>[^\p{C}\p{S}0-9;\'\/#%-])/u';  //Blacklisting regular expression.
    
    /* Constructor */
    public function __construct()
    {
        $phpFEMA = [
                        'password'  => 'Invalid entry!',
                        'password0' => 'Invalid entry!',
                        'password1' => 'Invalid entry!',
                        'password2' => 'Invalid entry!'
                   ];
        
        $validationMA = ['password' => ['kind' => 'password', 'type' => 'string', 'min' => 10, 'max' => 50, 'pattern' => '/(?>\A[A-Za-z0-9:*_=^~|@$&amp;!?+., ]{1,50}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL]];
        parent::__construct([], $phpFEMA, $validationMA, []);
    }
    
    
    /* Validators*/
    
    /* Mutators */
    private function setIndexedPHPFilterInstructions()
    {        
        for($i = 0, $length = count($this->filteredInputArray); $i <= $length; ++$i)
        {
            $index = " {$i}";
            $this->phpFieldValidationArray[$index] = ['filter'  => FILTER_VALIDATE_REGEXP, 
                                                      'flags'   => FILTER_REQUIRE_SCALAR,
                                                      'options' => ['regexp' => $this->passwordRegex]];
        }
        
        return;
    }
    
    private function setNamedPHPFilterInstructions()
    {
        foreach(array_keys($this->filteredInputArray) as $key)
        {
            $this->phpFieldValidationArray[$key] = ['filter'  => FILTER_VALIDATE_REGEXP, 
                                                    'flags'   => FILTER_REQUIRE_SCALAR, 
                                                    'options' => ['regexp' => $this->passwordRegex]];
        }
        
        return;
    }

    public function setFilteredInputArray(array $passwords)
    {
        $this->filteredInputArray = $passwords;
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
        foreach($this->filteredInputArray as $key => $password)
        {
            $this->testResultsArray[$key] = $this->password($password, $this->validationMetaArray['password'], $this->errorMessagesArray[$key]);
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
        
        return false;
    }
    
    private function passwordHasRequiredChars($password, &$errorMessages)
    {
        if(preg_match('/\A[A-Z]+?\z/', $password) &&
                preg_match('/\A[a-z]+?\z/', $password) &&
                preg_match('/\A[0-9]+?\z/', $password) &&
                preg_match('/\A[:*_=^~|@$&!?+., ]+?\z/', $password))
        {
            return true;
        }

        $errorMessage = 'Invalid password!';
        return false;
    }
    
    private function password($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            if($this->passwordHasRequiredChars($string, $errorMessage))
            {
                return true;
            }
        }
        
        return false;
    }
}
?>