<?php
namespace Isaac\Validators;

require_once 'Validator.php';
require 'TValidators.php';

class JoinValidatorPost extends Validator
{
    /* Traits */
    use TIsaacValidators
    {
        phone as protected;
        subject as protected;
        message as protected;
    }

    //Arrays
    //private $messageChars = [];
    private $tokens   = NULL;
    private $captchas = NULL;
    private $names    = NULL;
    private $emails   = NULL;
    
    //Objects
    private $formTokenValidator = NULL;
    private $captchaValidator   = NULL;
    private $nameValidator      = NULL;
    private $emailValidator     = NULL;
    
    //Blacklisting Regular Expressions
    private $joinBtnRegex = '/(?>[^(\A\z)\p{C}\p{M}\p{N}\p{P}\p{S}\p{Z}0-9;\' %-])/u';
    private $phoneRegex   = '/(?>\A\z|[^\p{C}\p{Pc}\p{Pe}\p{Pf}\p{S}\p{Z}A-Za-z;\' %]){1}?/u';
    private $orgNameRegex = '/(?>\A\z|[^\p{C}_;%]){1}?/u';
    private $messageRegex = '/(?>[^(\A\z)]){1}?/u';
    
    /*Constructor*/
    public function __construct(Validator $formTokenValidator, Validator $captchaValidator, Validator $nameValidator, Validator $emailValidator, array $filteredInput, array $transitoryInputs = NULL)
    {
        $phpFVA = [
                      'ajaxFlag'     => ['filter'  => FILTER_VALIDATE_INT,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['min_range' => 1, 'max_range' => 1]],
                      'nocache'      => ['filter'  => FILTER_VALIDATE_INT,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['min_range' => 0, 'max_range' => 1000000]],
                      'joinBtn'      => ['filter'  => FILTER_VALIDATE_REGEXP,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['regexp' => $this->joinBtnRegex]],
                      'memberType'   => ['filter'  => FILTER_VALIDATE_INT,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['min_range' => 0, 'max_range' => 1]],
                      'memberOption' => ['filter'  => FILTER_VALIDATE_INT,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['min_range' => 0, 'max_range' => 2]],
                      'payOption'    => ['filter'  => FILTER_VALIDATE_INT,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['min_range' => 0, 'max_range' => 8]],
                      'orgName'      => ['filter'  => FILTER_VALIDATE_REGEXP,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['regexp' => $this->orgNameRegex]],
                      'phone'        => ['filter'  => FILTER_VALIDATE_REGEXP,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['regexp' => $this->phoneRegex]],
                      'subject'      => ['filter'  => FILTER_VALIDATE_INT,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['min_range' => 1, 'max_range' => 5]],
                      'message'      => ['filter'  => FILTER_VALIDATE_REGEXP,
                                         'flags'   => FILTER_REQUIRE_SCALAR,
                                         'options' => ['regexp' => $this->messageRegex]]
                  ];
        
        $phpFEMA = [
                        'ajaxFlag'     => 'Illegal AJAX flag!',
                        'nocache'      => 'Illegal nocache value!',
                        'joinBtn'      => 'Bad button entry. Try again.',
                        'memberType'   => 'Bad member type.',
                        'memberOption' => 'Bad member option.',
                        'payOption'    => 'Bad pay option.',
                        'orgName'      => 'Bad organization name.',
                        'phone'        => 'Numbers (0-9) and dashes (-) only.',
                        'subject'      => 'Invalid selection. Try again.',
                        'message'      => 'Please enter a message.',
                   ];
        
        $validationMA = [
                            'ajaxFlag'       => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 1, 'rangeOfValues' => NULL],
                            'nocache'        => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1000000, 'pattern' => '/(?>\A[0-9]{1,7}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL],
                            'joinBtn'        => ['kind' => 'word', 'type' => 'string', 'min' => 4, 'max' => 4, 'pattern' => '/(?>\A(?:Join){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 'Join', 'rangeOfValues' => NULL],
                            'memberType'     => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1, 'pattern' => '/(?>\A[0-1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => [0, 1]],
                            'memberOption'   => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 2, 'pattern' => '/(?>\A[0-2]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => [0, 1, 2]],
                            'payOption'      => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 8, 'pattern' => '/(?>\A[0-8]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => [0, 1, 2, 3, 4, 5, 6, 7, 8]],
                            'orgName'        => ['kind' => 'opttitle', 'type' => 'string', 'min' => 1, 'max' => 150, 'pattern' => '/(?>\A[A-Za-z0-9:#@$&!?+\/.,\'-]{1}?[A-Za-z0-9:#@$&!?+\/.,\' -]{0,149}?){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'phone'          => ['kind' => 'optphone', 'type' => 'string', 'min' => 12, 'max' => 12, 'pattern' => '/(?>\A[2-9]{1}?[0-9]{2}?\-{1}?[2-9]{1}?[0-9]{2}?\-{1}?[0-9]{4}?\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'subject'        => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 5, 'pattern' => '/(?>\A[1-7]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => [1, 2, 3, 4, 5]],
                            'message'        => ['kind' => 'text', 'type' => 'string', 'min' => 1, 'max' => 1000, 'pattern' => '/(?>\A[A-Za-z0-9~,.:;\'"`!|_?@#=$%*&^\(\)[\]+{}\/<>\r\n -]{1,1000}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL]
                        ];
        
        $validatorTargets = [
                                'formTokenValidator' => ['token'],
                                'captchaValidator'   => ['captcha'],
                                'emailValidator'     => ['email1', 'email2'],
                                'nameValidator'      => ['firstname', 'lastname']
                            ];
        
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInput, $validatorTargets, $transitoryInputs);
        $this->formTokenValidator = $formTokenValidator;
        $this->captchaValidator   = $captchaValidator;
        $this->nameValidator      = $nameValidator;
        $this->emailValidator     = $emailValidator;
    }


    /*Accessors*/
    public function getMessageChars()
    {
        return $this->messageChars;
    }

    private function formConstantsEmpty()
    {
        return  ($this->filteredInputArray['firstname'] === '') && 
                ($this->filteredInputArray['lastname'] === '') && 
                ($this->filteredInputArray['email1'] === '') && 
                ($this->filteredInputArray['email2'] === '') &&
                ($this->filteredInputArray['phone'] === '') &&
                ($this->filteredInputArray['subject'] === '') && 
                ($this->filteredInputArray['message'] === '') && 
                ($this->filteredInputArray['captcha'] === '');
    }
    
    private function determineFormSubmissionState()
    {
        $flag = 0;
        
        if(($this->filteredInputArray['memberType'] === '0') && ($this->filteredInputArray['memberOption'] !== '2'))
        {
            if($this->formConstantsEmpty())
            { 
                $flag = 1;
            }
        }
        elseif(($this->filteredInputArray['memberType'] === '0') && ($this->filteredInputArray['memberOption'] === '2'))
        {
            if(($this->filteredInputArray['joinBtn'] === 'Join') && $this->formConstantsEmpty())
            { 
                $flag = 2;
            }
        }
        elseif(($this->filteredInputArray['memberType'] === '1'))
        {
            if(($this->filteredInputArray['orgName'] === '') && $this->formConstantsEmpty())
            { 
                $flag = 3;
            }
        }
        
        return $flag;
    }
    
    /*Helper Methods*/
    protected function isVoidInput()            //Test for blank form submission.
    {   
        $formState = $this->determineFormSubmissionState();
        
        if($formState !== 0)
        {
            $mandatoryFieldsArray     = ['firstname', 'lastname', 'email1', 'email2', 'message', 'captcha']; //orgName
            $moreMandatoryFieldsArray = ['subject'];
            $optionalFieldsArray      = ['phone'];
            
            if($formState === 3) //Empty Institutional Member Form
            {
                array_unshift($mandatoryFieldsArray, 'orgName');
            }

            //Provide error messages and for mandatory input fields (text, email, url, tel.
            foreach($mandatoryFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = 'You must fill this in.';
                $this->testResultsArray[$value]   = false;
                
                //Free up resources.
                $this->validationMetaArray[$value]       = NULL;
                $this->phpFieldValidatationArray[$value] = NULL;
                $this->phpFieldErrMsgsArray[$value]      = NULL;
                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
            }
            
            $mandatoryFieldsArray = NULL;
            unset($mandatoryFieldsArray);
            
            //Provide error messages and for mandatory fields.
            foreach($moreMandatoryFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = 'You must make a selection.';
                $this->testResultsArray[$value]   = false;
                $this->filteredInputArray[$value] = '';
                
                //Free up resources.
                $this->validationMetaArray[$value]       = NULL;
                $this->phpFieldValidatationArray[$value] = NULL;
                $this->phpFieldErrMsgsArray[$value]      = NULL;
                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
            }

            $moreMandatoryFieldsArray = NULL;
            unset($moreMandatoryFieldsArray);
            
            foreach($optionalFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = '' ;
                $this->testResultsArray[$value]   = true;
                
                //Free up resources.
                $this->validationMetaArray[$value]       = NULL;
                $this->phpFieldValidatationArray[$value] = NULL;
                $this->phpFieldErrMsgsArray[$value]      = NULL;
                unset($this->validationMetaArray[$value], $this->phpFieldValidatationArray[$value], $this->phpFieldErrMsgsArray[$value]);
            }
            
            //Free up resources.
            $this->validationMetaArray       = NULL;
            $this->phpFieldValidatationArray = NULL;
            $this->phpFieldErrMsgsArray      = NULL;
            $optionalFieldsArray             = NULL;
            unset($this->validationMetaArray, $this->phpFieldValidationArray, $this->phpFieldErrMsgsArray, $optionalFieldsArray);
            return true;
        }
        
        return false;
    }

    private function secondaryMessageValidator($minMessageChars, $maxMessageChars)  //Get set the $messageChar character counts.
    {
        $numChars = strlen($this->filteredInputArray['message']);   //Count the message characters.
            
        if($numChars < $minMessageChars)  //Less than 25 chars submitted.
        {
            $this->messageChars['numCharsEntered']        = $numChars;
            $this->messageChars['numCharsUnderOrOver']    = $numChars - $minMessageChars;
        }
        elseif($numChars > $maxMessageChars) //More than 1000 chars submitted.
        {
            $this->messageChars['numCharsEntered']        = $numChars;
            $this->messageChars['numCharsUnderOrOver']    = '+' . ($numChars - $maxMessageChars);
        }
        else 
        {
            $this->messageChars['numCharsEntered']        = $numChars;
            $this->messageChars['numCharsUnderOrOver']    = '&nbsp;';
        }
        
        return;
    }

    protected function translateValidatedInput()  //Translate form inputs into database data.
    {
        $memberType   = [0,1];
        $memberOption = [0,1,2];
        $payOption    = [0,1,2,3,4,5,6,7,8];
        $subject      = [
                            'I want to become a member.',
                            'How can I volunteer?',
                            'How can I learn more about ISAAC?',
                            'I like your new website!',
                            'Other'
                        ];
        //$newsletter   = [0,1]
        
        $this->translatedInputArray = $this->filteredInputArray; 
        $this->translatedInputArray['memberType']   = $memberType[$this->filteredInputArray['memberType']];
        $this->translatedInputArray['memberOption'] = $memberType[$this->filteredInputArray['memberOption']];
        $this->translatedInputArray['payOption']    = $memberType[$this->filteredInputArray['payOption']];
        $this->translatedInputArray['subject']      = $subject[$this->filteredInputArray['subject']];
        //$this->translatedInputArray['newsletter']   = $newsletter[$this->filteredInputArray['newsletter']];
        
        //Remove unnecessary data from the translated array.
        $this->translatedInputArray['joinBtn'] = NULL;
        $this->translatedInputArray['email2']  = NULL;
        unset($this->translatedInputArray['joinBtn'], $this->translatedInputArray['email2']);
        return;
    }
    
    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = ($this->names + $this->emails + $this->filteredInputArray);
    }
    
    protected function myValidator()            //The wrapper for the task of validating form inputs.
    {           
        //---------FORM TOKEN VALIDATION---------------
        
        $this->tokens = $this->extractFilteredElements($this->validatorTargets['formTokenValidator']);
        $this->formTokenValidator->setFilteredInputArray($this->tokens);
        
        //Free up resources.
        $this->tokens = NULL;
        unset($this->tokens);
       
        $this->formTokenValidator->validate();  //Failure is fatal.
        
        //----------CAPTCHA VALIDATION-----------------
        
        $this->captchas = $this->extractFilteredElements($this->validatorTargets['captchaValidator']);
        $this->captchaValidator->setFilteredInputArray($this->captchas);
        
        //Free up resources.
        $this->captchas = NULL;
        unset($this->captchas);
        
        if(!$this->captchaValidator->validate())
        {
            return false;  //Short-circuit the validation.
        }
        
        //--------------------------------------------
        
        if($this->isVoidInput())  //Blank form submission test.
        {
            return false;
        }
          
        $this->names  = $this->extractFilteredElements($this->validatorTargets['nameValidator']);
        $this->emails = $this->extractFilteredElements($this->validatorTargets['emailValidator']);
        
        /************ Use PHP FILTER functions to validate input.**************/
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);
        
        //Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);

        //Free up resources.
        $this->phpFieldErrMsgsArray = NULL;
        $phpFilterResults           = NULL;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);
        /**********************************************************************/
        
        /*******************Use personsal validation methods.******************/

        //Validate everything else using "variable" functions / methods.
        $this->coreValidatorLogic($this->filteredInputArray);

        // Free up resources.
        $this->validationMetaArray = null;
        unset($this->validationMetaArray);
        //--------------------------------------
        
        //Validate "firstname" and "lastname".
        $this->nameValidator->setFilteredInputArray($this->names);
        $this->nameValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->nameValidator, $this->names);

        //--------------------------------------
        
        //Validate "email1" and "email2".
        $this->emailValidator->setFilteredInputArray($this->emails);
        $this->emailValidator->validate();
        $this->mergeValidatorTestResultsAndMessages($this->emailValidator, $this->emails);
        //--------------------------------------

        /**********************************************************************/

        //Merge the names, emails, and other arrays together again.
        $this->mergeFilteredInputArrays();

        if(!in_array(false, $this->testResultsArray, true))
        {
            $this->translateValidatedInput();
            return true;
        }

        //$this->secondaryMessageValidator(1, 1000);
        return false;
    }
    
    /*Unique Field Validator Functions*/  
    protected function memberType($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return ($this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage));
    }
    
    protected function memberOption($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return ($this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage));
    }
    
    protected function payOptions($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return ($this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage));
    }
    
    protected function orgName($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return ($this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage));
    }
    
    protected function joinBtn($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return ($this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage));
    }
}
?>