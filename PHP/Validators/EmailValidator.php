<?php
declare(strict_types=1);

namespace tfwd\Validators;

require_once 'Validator.php';

/**
 * A class for validating email addresses along many dimensions.
 */
class EmailValidator extends Validator
{
    /* Properties */

    //Arrays
    private $goodDomains;
    private $badDomains;

    //Objects
    private $string;

    /*Constructor*/
    public function __construct(String $string) 
    {
        $phpFEMA = [
            'email'    => 'Illegal characters. Try again.',
            'email1'   => 'Illegal characters. Try again.',
            'email2'   => 'Illegal characters. Try again.',
            'username' => 'Invalid entry!'
        ];
        
        //Validation Meta Array Note: The pattern element allows consecutive periods. Use defense in depth until fixed.
        $validationMA = ['email' => ['kind' => 'email', 'type' => 'string', 'min' => 6, 'max' => 128, 'pattern' => '/(?>\A[A-Za-z0-9_-][A-Za-z0-9_.-]{0,62}?[A-Za-z0-9_-]{0,1}@{1}?(?:(?:[A-Za-z0-9]{1}?){1}?(?:[A-Za-z0-9.-]{0,61}?[A-Za-z0-9]{1}?){0,1}?){1,127}?\.{1}?[a-z]{2,20}?\z){1}?/u', 'noEmptyString' => true, 'specificValue' =>  false, 'rangeOfValues' => null]];
       
        parent::__construct([], $phpFEMA, $validationMA, []);
        $this->string = $string;
    }

    /* Validators*/

    /* Mutators */    
    public function setIndexedPHPFilterInstructions()
    {        
        for ($i = 0, $length = count($this->filteredInputArray); $i < $length; ++$i) {
            $index = " {$i}";
            $this->phpFieldValidationArray[$index] = ['filter' => FILTER_VALIDATE_EMAIL, 'flags' => FILTER_REQUIRE_SCALAR];
        }
    }

    public function setNamedPHPFilterInstructions()
    {
        foreach (array_keys($this->filteredInputArray) as $key) {
            $this->phpFieldValidationArray[$key] = ['filter' => FILTER_VALIDATE_EMAIL, 'flags' => FILTER_REQUIRE_SCALAR];
        }
    }

    public function setFilteredInputArray(array $emailAddresses)
    {
        $this->filteredInputArray = $emailAddresses;
        $this->programValidator();
    }

    /**
     * Checks for empty strings.
     */
    protected function isVoidInput()
    {
        return;
    }

    /**
     * Saves the list of all the domains found during processing.
     */
    protected function translateValidatedInput()
    {
        $this->translatedInputArray = $this->filteredInputArray;   //The domains of the email addresses.
        return;
    }

    /**
     * The overriding, core logic for vailidating e-mail address.
     */
    protected function coreValidatorLogic()
    {
        foreach ($this->filteredInputArray as $key => $emailAddress) {
            if ($this->testResultsArray[$key] === true) { //Only check the ones that passed the PHP Filter validation.
                $this->testResultsArray[$key] = $this->email($emailAddress, $this->validationMetaArray['email'], $this->errorMessagesArray[$key]);
            }
        }

        $this->validationMetaArray = null;
        unset($this->validationMetaArray);
    }

    /**
     * A method that looks for DNS MX records.
     * Returns array of bad domains.
     */
    private function mxDNSPing(array $uniqueDomains)
    {   
        $badDomains = [];
        
        foreach ($uniqueDomains as $key => $domain) {
            if (!checkdnsrr($domain, 'MX')) {
                $this->testResultsArray[$key] = false;
                $this->errorMessagesArray[$key] = 'No DNS MX records found.';
                $badDomains[$key] = $domain;
            }
        }

        return $badDomains;
    }

    /**
     * A method that returns an array of unique domain names.
     */
    private function removeDuplicateDomains(array $domains)
    {
        /**
         * array_unique() sorts the values, but preserves the indexes.
         * Then, it keeps the 1st unique value of each group. The input array is unaltered.
         */

        return array_unique($domains, SORT_STRING);
    }

    /**
     * A method that returns an array of bad email domains using DNS.
     */
    private function getBadEmailDomains(array $domains)
    {
        return $this->mxDNSPing($this->removeDuplicateDomains($domains));
    }

    /**
     * A method that returns an array of email domains.
     */
    private function getGoodEmailDomains()   //Verify email address domains through DNS.
    {   
        $domains = [];

        foreach ($this->filteredInputArray as $key => $emailAddress) {
            if ($this->testResultsArray[$key] === true) { //Get the domains of good email addresses only!
                $domains[$key] = $this->string->getEmailDomainPart($emailAddress);
            }
        }

        return $domains; //This includes duplicate domains!
    }

    /**
     * A method that manages DNS verification of e-mail domains.
     * @returns array
     */
    private function secondaryEmailValidationLogic()
    {
        $goodDomains = $this->getGoodEmailDomains();
        $badDomains  = $this->getBadEmailDomains($goodDomains);

        foreach ($goodDomains as $key => &$domain) {
            if (in_array($domain, $badDomains, true)) {
               $this->testResultsArray[$key]   = false;
               $this->errorMessagesArray[$key] = 'No DNS MX records found.';
               unset($goodDomains[$key]);
            }
        }
        unset($domain);

        $this->badDomains  = $badDomains;
        $this->goodDomains = $goodDomains;
    }

    private function sameEmailAddress()
    {
        if (count($this->filteredInputArray) === 2) {
            if ($this->identical($this->filteredInputArray['email1'], $this->filteredInputArray['email2'])) {
                return true;
            }
            
            $this->testResultsArray['email1'] = false;
            $this->testResultsArray['email2'] = false;
            $this->errorMessagesArray['email1'] = 'Does not match e-mail below.';
            $this->errorMessagesArray['email2'] = 'Does not match e-mail above.';
            return false;
        }

        if (count($this->filteredInputArray) === 1) {
            return true;
        }

        return false;
    }

    /**
     *A method that servers as the "main line" for validating e-mail addresses.
     *It is invoked by the super-class method, Validator::validate().
     */
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
        $phpFilterResults = null;
        unset($this->phpFieldErrMsgsArray, $phpFilterResults);

        /*******************Use personal validation methods.*******************/

        $this->coreValidatorLogic();            //Usee $this->mail() to validate e-mail addresses.

        if (!in_array(false, $this->testResultsArray, true)) {
            if ($this->sameEmailAddress()) {
                $this->secondaryEmailValidationLogic(); //Verifies email domain names using DNS MX records.
            }
        }

        /**********************************************************************/

        if (!in_array(false, $this->testResultsArray, true)) {
            $this->translateValidatedInput(); 

            //Free up resources.
            $this->filteredInputArray = null;
            unset($this->filteredInputArray);
            return true; 
        }

        return false;
    }

    /**
     * Finds problems with the local or domain parts of an e-mail address.
     */
    private function emailPartProblemFinder(string $emailAddress, string &$errorMessage)
    {
        $emailParts = $this->string->getEmailAddressParts($emailAddress);

        if (count($emailParts) !== 2) {
            $errorMessage = 'Invalid e-mail address!';
        } else {
            list ($localPart, $domain) = $emailParts;
            
            $localLength  = mb_strlen($localPart);
            $domainLength = mb_strlen($domain);

            if ($localLength === 0) {
                $errorMessage = 'Missing local part of address.';
            } elseif ($localLength > 64) {
                $errorMessage = 'Only 64 characters are alloed before the @ symbol ('.$localLength.' given)';
            } elseif (mb_strrpos($emailAddress, '.') === ($localLength - 1)) {
                $errorMessage = 'The local part of an email address cannot end with a period (.).';
            } elseif (mb_strpos($emailAddress, '..') >= 0) {
                $errorMessage = 'The local part of an email address cannot contain consecutive periods (..).';
            } elseif ($domainLength < 4) {//x.yy, is my minimum domain format.
                $errorMessage = 'Domain part < 4 characters. ('.$domainLength.' given)';
            } elseif ($domainLength > 253) {
                $errorMessage = 'Domain part exceeds 253 characters. ('.$domainLength.' given)';
            } else {
                $errorMessage = 'Invalid e-mail format';
            }
        }
    }

    /**
     * Finds problems with e-mail as a whole.
     */
    private function emailAddressProblemFinder(string $emailAddress, int $max, string &$errorMessage)
    {
        $length = mb_strlen($emailAddress);
        $atSymbolCount = mb_substr_count($emailAddress, '@', 'UTF-8');

        if ($length === 0) {
            return false;    //The reason was already assigned to the error message inside of $this->validateInput()
        } elseif ($length > 254) {
            $errorMessage = 'Exceeds max length ('.$max.' characters)';
        } elseif ((mb_strpos($emailAddress, '@') === 0)) {
            $errorMessage = 'Cannot start with a @';
        } elseif ((mb_strrpos($emailAddress, '@') === ($length - 1))) {
            $errorMessage = 'Cannot end with a @';
        } elseif ($atSymbolCount > 1) {
            $errorMessage = '@ appears '.$atSymbolCount.' times.';
        } elseif ((mb_strpos($emailAddress, '@') === false)) {
            $errorMessage = 'The @ symbol is missing.';
        } elseif (mb_strpos($emailAddress, '.') === 0) {
            $errorMessage = 'The local part of an email address cannot start with a period (.).';
        } else {
            $this->emailPartProblemFinder($emailAddress, $errorMessage);
        }
    }

    private function consecutivePeriodTest($emailAddress, &$errorMessage)
    {
        if (!preg_match('/\A(?!..)+?\z/', $emailAddress)) {
            return true;
        }

        $errorMessage = 'Consecutive periods are illegal!';
        return false;
    }

    /**
     * Validates email addresses..
     */
    private function email($string, array $validationMetaArray, &$errorMessage)
    {        
        extract($validationMetaArray);  //$kind, $type, $min, $pattern, $noEmptyString, $specificValue, $rangeOfValues

        if ($this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage)) {
            if ($this->consecutivePeriodTest($string, $errorMessage)) {
                return true;
            }
        }

        //Attempt to disover why the email address test failed.
        $this->emailAddressProblemFinder($string, $max, $errorMessage);

        return false;
    }
}
?>