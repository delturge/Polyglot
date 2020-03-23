<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class CaseStudyIdValidator extends Validator
{
    /* Properties */

    //Blacklisting Regular Expression.
    private $tokenRegex = '/(?>[^\p{C}\p{P}\p{S}\p{Z}g-z;\' %-])/u';
    
    /* Constructor */
    public function __construct()
    {
        $phpFVA = [
                      'casestudy' => ['filter'  => FILTER_VALIDATE_REGEXP,
                                      'flags'   => FILTER_REQUIRE_SCALAR,
                                      'options' => ['regexp' => $this->tokenRegex]]
                  ];
              
        $phpFEMA      = ['casestudy' => 'Illegal case study characters.'];
        $validationMA = ['casestudy' => ['kind' => 'digest', 'type' => 'string', 'min' => 1, 'max' => 32, 'pattern' => '/(?>\A[a-f0-9]{1,32}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL]];
        parent::__construct($phpFVA, $phpFEMA, $validationMA, []);
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
        $this->testResultsArray['casestudy'] = $this->casestudy($this->filteredInputArray['casestudy'], $this->validationMetaArray['casestudy'], $this->errorMessagesArray['casestudy']);
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
        
        error_log('Illegal case study ID submitted.');
        return false;
    }

    private function casestudy($string, array $validationMetaArray, &$errorMessage)  //A token that does not validate is a serious situation.
    {
        if(($this->testResultsArray['casestudy'] === true))
        {
            extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

            if($this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
            {
                return true;
            }
        }

        return false;
    }
}
?>