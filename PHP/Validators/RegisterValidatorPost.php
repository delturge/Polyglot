<?php
namespace Isaac\Validators;

require_once 'Validator.php';
require 'TFortressContactValidators.php';

class RegisterValidatorPost extends Validator
{
    /* Traits */
    use TFortressContactValidators
    {        
        website as protected;
        phone as protected;
        countryCode as protected;
        extension as protected;
        company as protected;
        address as protected;
        city as protected;
        state as protected;
        zip as protected;
        country as protected;
        jobLocation as protected;
        newsSubscribed as protected;
    }
    
    /* Properties */

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
    private $registerBtnRegex = '/(?>[^(\A\z)\p{C}\p{M}\p{N}\p{P}\p{S}\p{Z}0-9;\' %-])/u';
    /* private $phoneRegex       = '/(?>\A\z|[^\p{C}\p{Pc}\p{Pe}\p{Pf}\p{S}\p{Z}A-Za-z;\' %]){1}?/u'; */
    private $phoneRegex       = '/(?>[^(\A\z)\p{C}\p{Pc}\p{Pe}\p{Pf}\p{S}\p{Z}A-Za-z;\' %]){1}?/u';
    private $countryCodeRegex = '/(?>\A\z|[^\p{C}\p{Pc}\p{Pe}\p{Pf}\p{S}\p{Z}A-Za-z;\' %]){1}?/u';
    private $extensionRegex   = '/(?>\A\z|[^\p{C}\p{Pc}\p{Pe}\p{Pf}\p{S}\p{Z}A-Za-z;\' %]){1}?/u';
    /* private $companyRegex     = '/(?>\A\z|[^\p{C}_;%]){1}?/u'; */
    private $companyRegex     = '/(?>[^(\A\z)\p{C}_;%]){1}?/u';
    /* private $addressRegex     = '/(?>\A\z|[^\p{C}_;%]){1}?/u'; */
    private $addressRegex     = '/(?>[^(\A\z)\p{C}_;%]){1}?/u';
    /* private $cityRegex        = '/(?>\A\z|[^\p{C}0-9_;%]){1}?/u'; */
    private $cityRegex        = '/(?>[^(\A\z)\p{C}0-9_;%]){1}?/u';
    /* private $stateRegex       = '/(?>\A\z|[^\p{C}0-9_; %]){1}?/u'; */
    private $stateRegex       = '/(?>[^(\A\z)\p{C}0-9_; %]){1}?/u';
    /* private $zipRegex         = '/(?>\A\z|[^\p{C}A-Za-z;\' %]){1}?/u'; */
    private $zipRegex         = '/(?>[^(\A\z)\p{C}A-Za-z;\' %]){1}?/u';
    /* private $countryRegex     = '/(?>\A\z|[^\p{C}0-9;%]){1}?/u'; */
    private $countryRegex     = '/(?>[^(\A\z)\p{C}0-9;%]){1}?/u';
    private $jobLocationRegex = '/(?>\A\z|[^\p{C}0-9;%]){1}?/u';
    /* private $messageRegex     = '/(?>[^(\A\z)]){1}?/u'; */
    private $messageRegex     = '/(?>[^(\A\z)]){1}?/u';
    //private $websiteRegex = '/\A\z/';  //Only used when webSite field is the empty string.   

    /*Constructor*/
    public function __construct(Validator $formTokenValidator, Validator $captchaValidator, Validator $nameValidator, Validator $emailValidator, array $filteredInput, array $transitoryInputs = NULL)
    {        
        $phpFVA = [
                      'ajaxFlag'        => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 1, 'max_range' => 1]],
                      'nocache'         => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 0, 'max_range' => 1000000]],
                      'registerBtn'     => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->registerBtnRegex]],
                      'phone'           => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->phoneRegex]],
                      'countryCode'     => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->countryCodeRegex]],
                      'extension'       => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->extensionRegex]],
                      'company'         => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->companyRegex]],
                      'address'         => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->addressRegex]],
                      'city'            => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->cityRegex]],
                      'state'           => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->stateRegex]],
                      'zip'             => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->zipRegex]],
                      'country'         => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->countryRegex]],
                      'jobLocation'     => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->jobLocationRegex]],
                      'timeToContact'   => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 0, 'max_range' => 1]],
                      'contactPref'     => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 0, 'max_range' => 2]],
                      'phoneType'       => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 0, 'max_range' => 1]],
                      'subject'         => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 1, 'max_range' => 7]],
                      'message'         => ['filter'  => FILTER_VALIDATE_REGEXP,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['regexp' => $this->messageRegex]],
                      'newsSubscribed'  => ['filter'  => FILTER_VALIDATE_INT,
                                            'flags'   => FILTER_REQUIRE_SCALAR,
                                            'options' => ['min_range' => 0, 'max_range' => 1]]
                  ];
        
//        if($filteredInput['webSite'] === '')
//        {
//            $phpFVA['webSste'] = ['filter'  => FILTER_VALIDATE_REGEXP,
//                                  'flags'   => FILTER_REQUIRE_SCALAR,
//                                  'options' => ['regexp' => $this->webSiteRegex]];
//        }
        
        $phpFEMA = [
                        'ajaxFlag'       => 'Illegal AJAX flag!',
                        'nocache'        => 'Illegal nocache value!',
                        'registerBtn'    => 'Bad button entry. Try again.',
                        'phone'          => 'Bad input. Try again.',
                        'countryCode'    => 'Bad input. Try again.',
                        'extension'      => 'Bad input. Try again.',
                        'company'        => 'Bad input. Try again.',
                        'address'        => 'Bad input. Try again.',
                        'city'           => 'Bad input. Try again.',
                        'state'          => 'Bad input. Try again.',
                        'zip'            => 'Bad input. Try again.',
                        'country'        => 'Bad input. Try again.',
                        'jobLocation'    => 'Bad input. Try again.',
                        'timeToContact'  => 'Bad radio input. Try again',
                        'contactPref'    => 'Bad radio input. Try again.',
                        'phoneType'      => 'Bad radio input. Try again.',
                        'subject'        => 'Bad selection. Try again.',
                        'message'        => 'Bad input. Try again.',
                        'newsSubscribed' => 'Bad radio input. Try again.'
                   ];
        
        $validationMA = [
                            'ajaxFlag'       => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 1, 'pattern' => '/(?>\A[1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 1, 'rangeOfValues' => NULL],
                            'nocache'        => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1000000, 'pattern' => '/(?>\A[0-9]{1,7}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => false, 'rangeOfValues' => NULL],
                            'registerBtn'    => ['kind' => 'word', 'type' => 'string', 'min' => 8, 'max' => 8, 'pattern' => '/(?>\A(?:Register){1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' => 'Register', 'rangeOfValues' => NULL],
                            'phone'          => ['kind' => 'phone', 'type' => 'string', 'min' => 12, 'max' => 12, 'pattern' => '/(?>\A[2-9]{1}?[0-9]{2}?-{1}?[2-9]{1}?[0-9]{2}?-{1}?[0-9]{4}?\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'countryCode'    => ['kind' => 'optccode', 'type' => 'string', 'min' => 1, 'max' => 7, 'pattern' => '/(?>\A[+]{0,1}?[0-9]{1,3}?(?:[-]{1}?[0-9]{0,2}?){0,1}\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'extension'      => ['kind' => 'optextension', 'type' => 'string', 'min' => 1, 'max' => 5, 'pattern' => '/(?>\A[0-9]{1,5}?\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'company'        => ['kind' => 'company', 'type' => 'string', 'min' => 1, 'max' => 50, 'pattern' => '/(?>\A[A-Za-z0-9:#@$&!?+\/.,\'-]{1}?[A-Za-z0-9:#@$&!?+\/.,\' -]{1,49}?){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'address'        => ['kind' => 'address', 'type' => 'string', 'min' => 1, 'max' => 100, 'pattern' => '/(?>\A[A-Za0-9#]{1}?[A-Za-z0-9:#@$&!?+\/.,\' -]{1,99}?){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'city'           => ['kind' => 'city', 'type' => 'string', 'min' => 1, 'max' => 50, 'pattern' => '/(?>\A[A-Z]{1}?[A-Za-z\'. -]{1,49}?\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'state'          => ['kind' => 'state', 'type' => 'string', 'min' => 2, 'max' => 6, 'pattern' => '/(?>\A[A-Z]{2}?$|^[A-Z]{3}?$|^[A-Z]{2}?-[A-Z]{3}?\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'zip'            => ['kind' => 'zip', 'type' => 'string', 'min' => 5, 'max' => 10, 'pattern' => '/(?>\A(?>[0-9]{5}?|[0-9]{5}?-{1}?[0-9]{4})\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'country'        => ['kind' => 'country', 'type' => 'string', 'min' => 1, 'max' => 50, 'pattern' => '/(?>\A[A-Z]{1}?[a-z]{1}?[A-Za-z\' -]{0,48}?\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'jobLocation'    => ['kind' => 'optcityState', 'type' => 'string', 'min' => 1, 'max' => 100, 'pattern' => '/(?>\A[A-Z]{1}?[a-z]{1}?[A-Za-z\',. -]{1,90}?, (?:[A-Z]{2}?|[A-Z]{3}?|[A-Z]{2}?-[A-Z]{3}?){1}?\z){1}?/u', 'noEmptyString' => false, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'timeToContact'  => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1, 'pattern' => '/(?>\A[0-1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => [0, 1]],
                            'contactPref'    => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 2, 'pattern' => '/(?>\A[0-2]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => [0, 1, 2]],
                            'phoneType'      => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1, 'pattern' => '/(?>\A[0-1]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => [0, 1]],
                            'subject'        => ['kind' => 'integer', 'type' => 'int', 'min' => 1, 'max' => 7, 'pattern' => '/(?>\A[1-7]{1}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => [1, 2, 3, 4, 5, 6, 7]],
                            'message'        => ['kind' => 'text', 'type' => 'string', 'min' => 1, 'max' => 1000, 'pattern' => '/(?>\A[A-Za-z0-9~,.:;\'"`!|_?@#=$%*&^\(\)[\]+{}\/<>\r\n -]{1,1000}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => NULL],
                            'newsSubscribed' => ['kind' => 'integer', 'type' => 'int', 'min' => 0, 'max' => 1, 'pattern' => '/(?>\A[0-1]{1}?\z){1}?/', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => [0, 1]]
                        ];
        
//        $validationMA = [
//                            'website'             => ['kind' => 'opturl', 'type' => 'string', 'min' => 0, 'max' => 261, 'pattern' => '/\A(:?(?:[A-Za-z0-9]{1}?(?:[A-Za-z0-9-]{0,61}?[A-Za-z0-9]{1}?)??){1}?\.{1}?){1,127}?(?:[A-Za-z]{2,20}?)??\z/', 'noEmptyString' => false, 'specificValue' => false, 'rangeOfValues' => NULL]
//                        ];
        
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
    public function getTranslatedInputArray()
    {
        return $this->translatedInputArray;
    }
    
    
    /*Helper Methods*/
    protected function isVoidInput()              //Test for blank form submission.
    {
        if(($this->filteredInputArray['firstname'] === '') && 
                ($this->filteredInputArray['lastname'] === '') && 
                ($this->filteredInputArray['email1'] === '') && 
                ($this->filteredInputArray['email2'] === '') &&
                ($this->filteredInputArray['phone'] === '') &&
                ($this->filteredInputArray['countryCode'] === '') &&
                ($this->filteredInputArray['extension'] === '') &&
                ($this->filteredInputArray['company'] === '') &&
                ($this->filteredInputArray['address'] === '') && 
                ($this->filteredInputArray['city'] === '') && 
                ($this->filteredInputArray['state'] === '') && 
                ($this->filteredInputArray['zip'] === '') && 
                ($this->filteredInputArray['country'] === '') && 
                ($this->filteredInputArray['jobLocation'] === '') &&
                ($this->filteredInputArray['timeToContact'] === '0') && 
                ($this->filteredInputArray['contactPref'] === '0') && 
                ($this->filteredInputArray['phoneType'] === '0') && 
                ($this->filteredInputArray['subject'] === '') && 
                ($this->filteredInputArray['message'] === '') && 
                ($this->filteredInputArray['newsSubscribed'] === '1') &&
                ($this->filteredInputArray['captcha'] === ''))
        {   
            $mandatoryFieldsArray = ['firstname', 'lastname', 'email1', 'email2', 'captcha'];
            $optionalFieldsArray = ['phone', 'countryCode', 'extention', 'company', 'address', 'city', 'state', 'zip', 'country', 'jobLocation', 'subject', 'message'];

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
            
            foreach($optionalFieldsArray as $value)
            {
                $this->errorMessagesArray[$value] = '' ;
                $this->testResultsArray[$value]   = 'goodNodes';
                
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
    
    protected function translateValidatedInput()    //Translate form inputs into database data.
    {
        $this->translatedInputArray = $this->filteredInputArray; 
        
        $emailSubjectArray = [
                                '1' => 'I am looking for a certified installer.',
                                '2' => 'I want to use your products on my project.',
                                '3' => 'I want to give a testimonial.',
                                '4' => 'I need to transfer my warranty.',
                                '5' => 'I love Fortress Stabilization Systems.',
                                '6' => 'I like your web site',
                                '7' => 'Other'
                             ];

        /**
         * Data translations.
         */
        
        //User ID
        $this->translatedInputArray['user_id'] = NULL;
        
        //Best time to contact.
        $this->translatedInputArray['timeToContact'] = ($this->filteredInputArray['timeToContact'] === '1') ? 'P.M.' : 'A.M.';
        
        //Make temporary variable.
        $contactPref = $this->translatedInputArray['contactPref'];
        
        //Contact preference.
        if($contactPref === '0')
        {
            $this->translatedInputArray['contactPref'] = 'Email';
        }
        elseif($contactPref === '1')
        {
            $this->translatedInputArray['contactPref'] = 'Phone';
        }
        else
        {
            $this->translatedInputArray['contactPref'] = 'Mail';
        }
        
        //Free up resources.
        $contactPref = NULL;
        unset($contactPref);
        
        //Phone type.
        $this->translatedInputArray['phoneType'] = ($this->filteredInputArray['phoneType'] === '1') ? 'Landline' : 'Cellular';
        
        //E-mail subject.
        $this->translatedInputArray['subject'] = $emailSubjectArray[$this->filteredInputArray['subject']];

        //Newsletter subscription.
        $this->translatedInputArray['newsSubscribed'] = ($this->filteredInputArray['newsSubscribed'] === '1') ? 'Y' : 'N';
        
        //Remove unnecessary data from the translated array.
        $this->translatedInputArray['registerBtn'] = NULL;
        $this->translatedInputArray['email2']     = NULL;
        
        unset($this->translatedInputArray['registerBtn'],
                $this->translatedInputArray['email2']);

        return;
    }
    
    private function mergeFilteredInputArrays()
    {
        $this->filteredInputArray = ($this->names + $this->emails + $this->filteredInputArray);
    }

    protected function myValidator()              //The wrapper for the task of validating form inputs.
    {
        //---------FORM TOKEN VALIDATION---------------
        $this->tokens = $this->extractFilteredElements($this->validatorTargets['formTokenValidator']);
        
        //Validate "token".
        $this->formTokenValidator->setFilteredInputArray($this->tokens);
        
        //Free up resources.
        $this->tokens = NULL;
        unset($this->tokens);
        
        $this->formTokenValidator->validate();
        
        //----------CAPTCHA VALIDATION-----------------
        $this->captchas = $this->extractFilteredElements($this->validatorTargets['captchaValidator']);

        //Validate "captcha".
        $this->captchaValidator->setFilteredInputArray($this->captchas);
        
        //Free up resources.
        $this->captchas = NULL;
        unset($this->captchas);
        
        if(!$this->captchaValidator->validate())
        {
            return false;
        }
        //--------------------------------------------
        
        if($this->isVoidInput())  //Blank form submission test.
        {
            return false;
        }
        
        //Prune $this->filteredInputArray. Copy appropriate elements into new arrays.
        $this->names  = $this->extractFilteredElements($this->validatorTargets['nameValidator']);
        $this->emails = $this->extractFilteredElements($this->validatorTargets['emailValidator']);

        //Use PHP FILTER functions to validate input.
        $phpFilterResults = filter_var_array($this->filteredInputArray, $this->phpFieldValidationArray, true);
        
        //Check and interpret PHP FILTER validation results.
        $this->phpFilterErrToMesg($phpFilterResults, $this->phpFieldErrMsgsArray, $this->errorMessagesArray, $this->testResultsArray);

        //Free up resources.
        $this->phpFieldErrMsgsArray = NULL;
        $phpFilterResults           = NULL;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);

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
        /**********************************************************************/

        return false;
    }
    
    
    /*Unique Field Validator Functions*/
    protected function registerBtn($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validateInput($string, $kind, $type, $min, $max, $pattern,  $noEmptyString, $specificValue, $rangeOfValues, $errorMessage))
        {
            return true;
        }
        
        return false;
    }
}
