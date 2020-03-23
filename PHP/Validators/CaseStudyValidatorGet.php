<?php
namespace Isaac\Validators;

require_once 'Validator.php';

class CaseStudyValidatorGet extends Validator
{    
    /* Properties */

    //Arrays
    private $caseStudyIds = NULL; //There will only be one case study id per valid request.

    //Objects
    private $caseStudyIdValidator = NULL;
    
    //Blacklisting Regular Expression.
    private $paneRegex  = '/(?>[^\p{C}\p{N}\p{S}0-9;_%\'-])/u';
    private $panelRegex = '/(?>[^\p{C}\p{N}\p{S}0-9;_%\'-])/u';
    
    /* Constructor */
    public function __construct(Validator $caseStudyIdValidator, array $filteredInput, array $transitoryInputs = NULL)
    {
        $phpFVA = [
                      'pane'  => ['filter'  => FILTER_VALIDATE_REGEXP,
                                  'flags'   => FILTER_REQUIRE_SCALAR,
                                  'options' => ['regexp' => $this->paneRegex]],
                      'panel' => ['filter'  => FILTER_VALIDATE_REGEXP,
                                  'flags'   => FILTER_REQUIRE_SCALAR,
                                  'options' => ['regexp' => $this->panelRegex]]
                  ];

        $phpFEMA = [
                        'pane'  => 'Illgal character in case study category!',
                        'panel' => 'Illgal character in case study sub-category!',
                   ];
        
        $validationMA = [
                            'pane'  => ['kind' => 'text', 'type' => 'string', 'min' => 1, 'max' => 50, 'pattern' => '/(?>\A[A-Z]{1}?[A-Za-z ]{0,49}\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL],
                            'panel' => ['kind' => 'text', 'type' => 'string', 'min' => 1, 'max' => 100, 'pattern' => '/(?>\A[A-Z]{1}?[A-Za-z ]{0,99}\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL]
                        ];

        $validatorTargets = ['caseStudyIdValidator' => ['casestudy']];
        
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInput, $validatorTargets, $transitoryInputs);
        $this->caseStudyIdValidator = $caseStudyIdValidator;
    }

    protected function isVoidInput() //Test for blank form submission.
    {
        ;
    }
    
    protected function translateValidatedInput()
    {
        $this->translatedInputArray = $this->filteredInputArray;
    }
    
    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = array_merge($this->caseStudyIds, $this->filteredInputArray);
    }
     
    /* Protected Methods */
    protected function myValidator()
    {
        //---------CASE STUDY ID VALIDATION---------------
        $this->caseStudyIds = $this->extractFilteredElements($this->validatorTargets['caseStudyIdValidator']);
        $this->caseStudyIdValidator->setFilteredInputArray($this->caseStudyIds);
        $this->caseStudyIdValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->caseStudyIdValidator, $this->caseStudyIds);
        //-------------------------------------------------
        
        if($this->isVoidInput())  //Blank form submission test.
        {
            return false;
        }

        //Use PHP FILTER functions to validate input.
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);
        
        //Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);
        
        //Free up resources.
        $this->phpFieldErrMsgsArray = NULL;
        $phpFilterResults = NULL;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);

        /*******************Use personsal validation methods.******************/
        
        //Validate everything else using "variable" functions / methods.
        $this->coreValidatorLogic();
        
        //Free up resources.
        $this->validationMetaArray = NULL;
        unset($this->validationMetaArray);

        //--------------------------------------

        $this->mergeFilteredInputArrays();
        
        //Free up resources.
        $this->caseStudyIds = NULL;
        unset($this->caseStudyIds);
                
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
    
    public function pane($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            return true;
        }
        
        return false;
    }
    
    public function panel($string, array $validationMetaArray, &$errorMessage)
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