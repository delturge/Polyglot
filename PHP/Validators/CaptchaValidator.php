<?php
namespace Isaac\Validators;

class CaptchaValidator extends Validator
{
    /* Properties */

    //Blacklisting Regular Expression.
    private $captchaRegex = '/(?>[^\p{C}\p{S}\p{Z}% \';-])/u';
    
    /* Constructor */
    public function __construct()
    {
        $phpFVA = [
                      'captcha' => ['filter'  => FILTER_VALIDATE_REGEXP,
                                    'flags'   => FILTER_REQUIRE_SCALAR,
                                    'options' => ['regexp' => $this->captchaRegex]]
                  ];

        $phpFEMA      = ['captcha' => 'Illegal characters. Try again.'];
        $validationMA = ['captcha' => ['kind' => 'captcha', 'type' => 'string', 'min' => 5, 'max' => 5, 'pattern' => '/(?>\A[A-Za-z0-9?!*&$#@+_=~]{5}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => $_SESSION['captchaAnswer'], 'rangeOfValues' => NULL]];
        
        parent::__construct($phpFVA, $phpFEMA, $validationMA, []);
    }
    
    /* Validators*/
    
    /* Mutators */
//    protected function setIndexedPHPFilterInstructions()
//    {
//        for($i = 0, $length = count($this->filteredInputArray); $i <= $length; ++$i)
//        {
//            $index = " {$i}";
//            $this->phpFieldValidationArray[$index] = ['filter'  => FILTER_VALIDATE_REGEXP, 
//                                                      'flags'   => FILTER_REQUIRE_SCALAR,
//                                                      'options' => ['regexp' => $this->captchaRegex]];
//        }
//
//        return;
//    }
//    
//    protected function setNamedPHPFilterInstructions()
//    {
//        foreach(array_keys($this->filteredInputArray) as $key)
//        {
//            $this->phpFieldValidationArray[$key] = ['filter'  => FILTER_VALIDATE_REGEXP, 
//                                                    'flags'   => FILTER_REQUIRE_SCALAR, 
//                                                    'options' => ['regexp' => $this->captchaRegex]];
//        }
//
//        return;
//    }
//
//    public function setFilteredInputArray(array $captcha)
//    {
//        $this->filteredInputArray = $this->isArrayOfStrings($captcha);
//        $this->programValidator();
//    }
    
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
        $this->testResultsArray['captcha'] = $this->captcha($this->filteredInputArray['captcha'], $this->validationMetaArray['captcha'], $this->errorMessagesArray['captcha']);
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
        
        error_log(print_r($this->errorMessagesArray,true));
        return false;
    }
    
    private function captcha($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            return true;
        }
        
        return false;
    }
}
