<?php
declare(strict_types=1);

namespace tfwd\Validators;

use tfwd\Exceptions\SecurityException as SecurityException;
use tfwd\Framework\Base as Base;
use tfwd\Validators\Validator as Validator;

/**
 * A universal class for final input validation.
 * UNDER REFACTORYING / CONSTRUCTION
 * 
 * @author Anthony E. Rutledge
 * @version 10-11-2018
 * @copyright (c) 2018, Time Flies Web Design
 */
abstract class Validator extends Base
{
                    /* Input */
    
     /**
     * The input data that requires testing.
     * 
     * @var array
     */
    protected $filteredData;
    
    /**
     * Holds field specific, PHP defined, validating filter instructions for
     * phase 1.
     * 
     * @link http://php.net/manual/en/filter.filters.validate.php
     * @var array
     */
    protected $phpFieldValidationArray;
    
    /**
     * Holds the error messages for PHP filter failures.
     * 
     * @var array
     */
    protected $phpFieldErrorMessages;
    
    /**
     * Holds user defined values (int limits, regular expressions, etc) used
     * to validate input. Two dimensional associative array. One array per field.
     * 
     * @var array
     */
    protected $validationMetaArray;
    
    /**
     * An array that tells injected / internal Validator, child objects
     * which filtered data elements to validate.
     * Example: An EmailValidator would validate all email addresses elements.
     * 
     * @var array
     */
    protected $validatorTargets;

                    /* Output */
    
    /**
     * Holds the output in a format ready to use by the Model.
     * 
     * @var array mixed
     */
    protected $cleanData;

    /**
     * Holds the results of validation tests.
     * 
     * @var array bool[]
     */
    protected $testResults = [];
    
    /**
     * Holds the results of validation tests.
     * 
     * @var array string[]
     */
    protected $errorMessages = [];

                    /* Abstract Methods */
    
    /**
     * A method that checks to see if no user input was supplied at all,
     * excluding form tokens.
     * 
     * @returns bool
     */
    abstract protected function isVoidInput();
    
    /**
     * A method that is called after final validation is complete.
     * Provides flexibility so that forms can be submitted with minimal data.
     * Void input submissions are to be rejected.
     * 
     * @return array
     */
    abstract protected function translateData();
    
    /**
     * A method that defines a scenario specific sequence of validation.
     * Typically, using the strategy pattern, each concrete child of Validator
     * will use this as their "main line".
     * 
     * @return bool
     */
    abstract public function test();

                               /* Controcutor */
    
    /**
     * The Validator, abstract super class constructor.
     * 
     * @param array $filteredData The input data that requires testing.
     * @param array $phpFVA The PHP validating filter instructions for phase 1.
     * @param array $phpFEMA The user defined error messages for PHP validating filter errors.
     * @param array $validationMA The user defined validation parameters for each field. Two dimensional array.
     * @param array $validatorTargets THe 
     */
    public function __construct(array $filteredData, array $phpFVA, array $phpFEMA, array $validationMA, array $validatorTargets = null) 
    {
        $this->filteredData = $filteredData;
        $this->pruneValidator($phpFVA, $phpFEMA, $validationMA); // Removes validation instructions for elements that are allowed to be absent.
        $this->phpFieldValidationArray = $phpFVA;
        $this->phpFieldErrorMessages = $phpFEMA;
        $this->validationMetaArray = $validationMA;
        $this->validatorTargets = $validatorTargets;
        mb_internal_encoding('UTF-8'); // More of a "better safe than sorry thing."
    }

    /**************************************************************************/
    
                               /* Accessors */

    /**
     * A method that returns all tested data.
     * 
     * @return array
     */
    public function getCleanData()
    {
        return $this->cleanData;
    }
    
    /**
     * A method that returns one tested value.
     * 
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getCleanValue(string $key)
    {
        if (!isset($this->cleanData[$key])) {
            throw new \InvalidArgumentException('The requested element does not exists in the filtered input array.');
        }

        return $this->cleanData[$key];
    }

    /**
     * A method that returns all the test results.
     * 
     * @return array
     */
    public function getTestResults()
    {
        return $this->testResults;
    }
    
    /**
     * A method that returns one test result.
     * 
     * @param string $key
     * @return type
     * @throws \InvalidArgumentException
     */
    public function getTestResult(string $key)
    {
        if (!isset($this->testResults[$key])) {
            throw new \InvalidArgumentException('The requested element does not exists in the test results array.');
        }

        return $this->testResults[$key];
    }
    
    /**
     * A method that returns all the error messages.
     * 
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * A method that returns one error message.
     * 
     * @param string $key
     * @return type
     * @throws \InvalidArgumentException
     */
    public function getErrorMessage(string $key)
    {
        if (!isset($this->errorMessage[$key])) {
            throw new \InvalidArgumentException('The requested element does not exists in the error messages array.');
        }

        return $this->errorMessage[$key];
    }

    /**
     * Translates test results into CSS classes.
     * Only used when validation fails.
     * Returns CSS classes.
     * 
     * Note: Moving to "HtmlView"
     * 
     * @return array
     */
    public function getClasses(): array
    {
        $classes = [];

        foreach ($this->testResults as $key => $value) {
            if ($key === 'ajaxFlag' || $key === 'nocache') {  // These specific tests are nNo longer necessary in new setup.
                continue;
            }

            $classes[$key] = ($value === true) ? 'goodNodes' : 'badNodes';
        }

        return $classes;
    }

    /**
     * Translates test results into CSS classes.
     * Only used when validation fails.
     * Returns '1' and '0' codes for AJAX requests.
     * 
     * Note: Moving to "JsonView"
     * 
     * @return array
     */
    public function getAjaxClasses()  //Translates test results into CSS classes.
    {
        $classes = [];

        foreach ($this->testResults as $key => $value) {
            if ($key === 'ajaxFlag' || $key === 'nocache') {
                continue;
            }

            $classes[$key] = ($value === true) ? '1' : '0';
        }

        return $classes;
    }         

    /**************************************************************************/

                          /* Core validation methods. */

    /**
     * A method that comparea a value to true.
     * 
     * @param type $value
     * @return bool
     */
    protected function isTrue(bool $value): bool
    {
        return ($value === true);
    }

    /**
     * A method that comparea a value to false.
     * 
     * @param bool $value
     * @return bool
     */
    protected function isFalse(bool $value): bool
    {
        return ($value === false);
    }
    
    /**
     * A method that tests to see if values are almost equivalent, via type juggling.
     * 
     * @param mixed $y1
     * @param mided $y2
     * @return bool
     */
    protected function similar($y1, $y2): bool
    {
        return ($y1 == $y2);
    }

    /**
     * A method that tests to see if values are the same datatype and value. No type juggling.
     * 
     * @param mixed $y1
     * @param mixed $y2
     * @return bool
     */
    protected function identical($y1, $y2): bool
    {
        return ($y1 === $y2);
    }

    /**
     * A method that determines the kind of input data, and
     * if the input value is 100% composed of visible characters
     * (no whitespace characters).
     * 
     * @param string $kind
     * @param string $input
     * @return bool
     */
    private function isVisible(string $kind, string $input): bool
    {
        $visibles = ['base64' => true, 'path' => true, 'file' => true, 'mimeType' => true, 'url' => true, 'email' => true, 'phone' => true, 'ccode' => true, 'ipAddress' => true, 'captcha' => true, 'state' => true, 'zip' => true];
        return isset($visibles[$kind]) && ctype_graph($input);
    }

    /**
     * A method that determines the kind of input data, and
     * if the input value is 100% composed of printable characters,
     * including spaces, tabs, and whitespace characters in general.
     * 
     * @param string $kind
     * @param string $input
     * @return bool
     */
    private function isPrintable(string $kind, string $input): bool
    {
        $printables = ['name' => true, 'password' => true, 'title' => true, 'userAgent' => true, 'text' => true, 'ccode' => true, 'company' => true, 'address' => true, 'city' => true, 'country' => true, 'cityState' => true];
        return isset($printables[$kind]) && ctype_print($input);
    }

    /**
     * A method that determines the kind of input data and
     * if the input value is 100% composed of visible, alpha-numeric characters.
     * 
     * @param string $kind
     * @param string $input
     * @return bool
     */
    private function isVisibleAlphaNumeric(string $kind, string $input): bool
    {
        $visibleAlphaNumerics = ['answer' => true, 'digest' > true];
        return isset($visibleAlphaNumerics[$kind]) && ctype_graph($input) && ctype_alnum($input);
    }

    /**
     * A method that determines the kind of input data, and
     * if the input value is 100% composed of visible, alphabetic characters.
     * 
     * @param string $kind
     * @param string $input
     * @return bool
     */
    private function isVisibleAlpha(string $kind, string $input): bool
    {
        $visibleAlphas = ['word' => true];
        return isset($visibleAlphas[$kind]) && ctype_graph($input) && ctype_alpha($input);
    }

    /**
     * A method that determines the kind of input data, and
     * if the input value is 100% composed of visible, digit characters.
     * 
     * @param string $kind
     * @param string $input
     * @return bool
     */
    private function isVisibleDigit(string $kind, string $input): bool
    {
        $visibleDigits = ['extension' => true];
        return isset($visibleDigits[$kind]) && ctype_graph($input) && ctype_digit($input);
    }

    /**
     * A method that determines if an input value is optional, based on
     * the scenario ($optional). Empty, but optional fields should cause
     * no errors.
     * 
     * @param string $input
     * @param bool $optional
     * @return bool
     */
    private function isOptional(string $input, string $optional): bool
    {
        return ($optional === true) && ($input === '');
    }

    /**
     * A method that tests if all elements of an array are strings.
     * 
     * @param array $strings
     * @return bool
     */
    protected function isArrayOfStrings(array $strings)
    {
        $result = true;
        
        foreach ($strings as $value) {
            if (!is_string($value)) {
                $result = false;
                break;
            }
        }

        return $result;
    }
    
    /**
     * A method that test if a string is valid for a scenario defined by
     * the method's input parameters in $this->validationMetaArray.
     * 
     * A majority of validations will use this method.
     * 
     * @param string $input
     * @param bool $optional
     * @param string $kind
     * @param int $length
     * @param int $min
     * @param int $max
     * @param string $regex
     * @param string $errorMessage
     * @return bool
     */
    private function stringTest(string $input, bool $optional, string $kind, int $length, int $min, int $max, string $regex, string &$errorMessage)
    {
        if ($this->isOptional($input, $optional)) {
            $errorMessage = '';
            return true;
        }

        if (!($this->isVisible($kind, $input) ||
                $this->isPrintable($kind, $input) ||
                $this->isVisibleAlphaNumeric($kind, $input) ||
                $this->isVisibleAlpha($kind, $input) ||
                $this->isVisibleDigit($kind, $input))) {
            $errorMessage = 'Invalid entry!';
            return false;
        }

        if ($length < $min) {
            $errorMessage = 'Too small! ('.$min.' min, ' .$length. ' given.)';
        } elseif ($length > $max) {
            $errorMessage = 'Too large! ('.$max.' max, ' .$length. ' given.)';
        } elseif (preg_match($regex, $input) === 0) {
            $errorMessage = 'Invalid string format!';
        } else {
            $errorMessage = '';
            return true;
        }

        return false;
    }

    /**
     * A method that test if an integer is valid for a scenario defined by
     * the method's input parameters.
     * 
     * @param string $input
     * @param bool $optional
     * @param int $integer
     * @param int $min
     * @param int $max
     * @param string $regex
     * @param string $errorMessage
     * @return bool
     */
    private function integerTest($input, $optional, $integer, $min, $max, $regex, &$errorMessage)
    {
        if ($this->isOptional($input, $optional)) {
            $errorMessage = '';
            return true;
        }

        if (!is_int($integer)) {
            $errorMessage = 'Invalid data type!';
            return false;
        }

        if ($input !== (string) $integer) {                        // Convert $int into a string to see if it matches original input.
            $errorMessage = 'Invalid input! (fake value)';
        } elseif (preg_match($regex, $input) === 0) {              // Regular Expression Test
            $errorMessage = 'Invalid input! (Bad number format)';
        } elseif ($integer < $min) {                               // Test against minimum value.
            $errorMessage = 'Invalid selection! (too low)';
        } elseif ($integer > $max) {                               // Test against maximum value.
            $errorMessage = 'Invalid selection! (too high)';
        } else {
            $errorMessage = '';
            return true;
        }

        return false;
    }

    /**
     * A method that test if an floating point number is valid for a scenario defined by
     * the method's input parameters.
     * 
     * UNDER CONSTRUCTION / REFACTORING: 
     * Must change floating point comparison logic.
     * Error messages must be more generic. Never came back to this.
     * 
     * @param string $input
     * @param bool $optional
     * @param float $float
     * @param int $min
     * @param int $max
     * @param string $regex
     * @param string $errorMessage
     * @return bool
     */
    private function floatTest($input, $optional, $float, $min, $max, $regex, &$errorMessage)
    {
        if ($this->isOptional($input, $optional)) {
            $errorMessage = '';
            return true;
        }

        if (!is_float($float)) {
            $errorMessage = 'Invalid data type!';
            return false;
        }

        if ($input !== (string) $float) {                         // Convert $float into a string to see if it matches original input.
            $errorMessage = 'Invalid input! (fake value)';
        } elseif (preg_match($regex, $input) === 0) {             // Regular expression test on the string version of the value.
            $errorMessage = 'Invalid input! (Bad number format)';
        } elseif ($float < $min) {
            $errorMessage = 'Invalid selection! (too low)';
        } elseif ($float > $max) {
            $errorMessage = 'Invalid selection! (too high)';
        } else {
            $errorMessage = '';
            return true;
        }

        return false;
    }

    /**
     * A method that determines if a non-empty string value is required 
     * (a real value must be submitted).
     * 
     * @param string $input
     * @param bool $optional
     * @return bool
     */
    private function isMissingMandatoryValue(string $input, bool $optional): bool
    {
        return ($optional === false) && ($input === '');
    }

    /**
     * A method that determines if any value will suffice for a field.
     * 
     * @param string $input
     * @param bool $noEmptyString
     * @param mixed  $specificValue false, or string, integer, or float
     * @param array $rangeOfValues
     * @return bool
     */
    private function isUnconstrainedValue(string $input, bool $noEmptyString, $specificValue, array $rangeOfValues = null): bool
    {
        return isset($input) && ($noEmptyString === false) && ($specificValue === false) && ($rangeOfValues === null);
    }

    /**
     * A method that determines if a specific value must be, and has been,
     * submitted.
     * 
     * @param string $input The user submitted value.
     * @param mixed  $specificValue false, or string, integer, or float.
     * @param array $rangeOfValues An array of valid values, or null.
     * @return bool
     */
    private function isSpecificValue($specificValue, array $rangeOfValues = null): bool
    {
        return ($specificValue !== false) && ($rangeOfValues === null);
    }

    /**
     * A method that determines if the input value is one of a pre-set range.
     * 
     * @param mixed $specificValue false, or string, integer, or float
     * @param array $rangeOfValues
     * @return bool
     */
    private function isInRangeOfValues($specificValue, array $rangeOfValues = null): bool
    {
        return ($specificValue === false) && is_array($rangeOfValues) && !empty($rangeOfValues);
    }

     /**
     * A method that determines if the input value is one value of a pre-set range
     * (in an array). 
     * 
     * @param string $input The value being examined / tested.
     * @param bool $noEmptyString Indicates if empty strings are permissable.
     * @param mixed $specificValue A specific value, or boolean false.
     * @param array $rangeOfValues A valid range of values that $input can be.
     * @param array $errorMessage By reference, the error message element from $this->errorMessages()
     * @return bool 
     */ 
    private function matchingTest($input, $optional, $noEmptyString, $specificValue, $rangeOfValues, &$errorMessage)
    {
        if ($this->isMissingMandatoryValue($input, $optional)) {
            $errorMessage = 'Must be filled in!';
        } elseif ($this->isUnconstrainedValue($input, $noEmptyString, $specificValue, $rangeOfValues)) {
            return true;
        } elseif ($this->isSpecificValue($specificValue, $rangeOfValues)) {
            if ($input === $specificValue) {
                return true;
            } 

            $errorMessage = 'Bad match!';
        } elseif ($this->isInRangeOfValues($specificValue, $rangeOfValues)) {
            if (in_array($input, $rangeOfValues, true)) {
                return true;
            }

            $errorMessage = 'Invalid option!';
        } else {
            $errorMessage = 'Invalid input!';
        }

        return false;
    }

     /**
     * A method that manages the validation of a unique input value. This
     * method is only called by field specific PHP variable functions / methods.
     * 
     * NEEDS REFACTORING: Might define separate String, Integer, and Float classes.
     * 
     * @param string $input The value being examined / tested.
     * @param bool $optional Describes if a value must be submitted.
     * @param string $kind A short classification of the input data.
     * @param type $type The datatype which should be used to inspect $input.
     * @param int $min The smallest length, or magnitude allowed.
     * @param int $max The biggest length, or magnitude allowed.
     * @param string $regex A Perl Compatible Regular Expression
     * @param bool $noEmptyString A flag that determines if an empty string is a valid value.
     * @param mixed $specificValue Indicates if the input must have a specific value.
     * @param mixed $rangeOfValues An array of possible values for the input, or null.
     * @return bool 
     */ 
    private function validateInput(&$input, $optional, $kind, $type, $min, $max, $regex, $noEmptyString = true, $specificValue = false, array $rangeOfValues = null, &$errorMessage = null): bool
    {
        $testResult = false;
        $tempVar = null;
        $length = mb_strlen($input);

        if (($type === 'string') && is_string($input)) {   
            $tempVar = $input;
            $testResult = $this->stringTest($input, $optional, $kind, $length, $min, $max, $regex, $errorMessage);
        } elseif (($type === 'int') && ctype_graph($input) && ctype_digit($input) && is_int((int) $input)) { // Remember, true integers include negative numbers!
            $tempVar = $this->getInt($input);
            $testResult = $this->integerTest($input, $optional, $tempVar, $min, $max, $regex, $errorMessage);
        } elseif (($type === 'float') && ctype_graph($input) && is_numeric($input) && is_float((float) $input)) { // I need to add a function that looks for one and only one decimal point, splits on that decimail point, and makes sure the parts are composed of integers.
            $tempVar = $this->getFloat($input);
            $testResult = $this->floatTest($input, $optional, $tempVar, $min, $max, $regex, $errorMessage);
        } elseif ($type === 'number') {    // This tests an actual (datatype wise) integer or float datatype. Re-examine this entire section.
            $tempVar = $input;

            if (is_int($input)) {
                $testResult = $this->integerTest((string) $input, $optional, $tempVar, $min, $max, $errorMessage);
            } elseif (is_float($input)) {
                $testResult = $this->floatTest((string) $input, $optional, $tempVar, $min, $max, $errorMessage);
            } else {
                $errorMessage = 'Invalid data entered!';
            }
        } else {
            $errorMessage = 'Invalid data entered!';
        }

        return $testResult && $this->matchingTest($tempVar, $optional, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }
    
     /**
     * A method that tests all user input against validation methods
     * (which are PHP variable functions / methods).
     * This is this user defined phase two of the validation sequence.
     * 
     * $this->validationMetaArray holds user defined validation values.
     * 
     * @param array $values The array of values to be tested. 
     */ 
    protected function coreValidatorLogic(array &$values)  // Called from within a concrete child's implementation of the test() method.
    {
        foreach ($values as $key => &$value) {
            if ($this->testRestults[$key] === false) { // If Phase 1 test failed.
                $this->validationMetaArray[$key] = null;   // No need to do a Phase 2 test.
                unset($this->validationMetaArray[$key]);
            } elseif (is_scalar($value)) {                 // Run Phase 2 test on a scalar value.
                
                /* This is where the PHP variable functions / methods are called for each field. */
                /*********************************************************************************/
                $this->testRestults[$key] = $this->$key($value, $this->validationMetaArray[$key], $this->errorMessages[$key]);
                /*********************************************************************************/
                
                $this->validationMetaArray[$key] = null;
                unset($this->validationMetaArray[$key]);
            } elseif (is_array($value)) {                 // Run Phase 2 test on an array.
                $this->coreValidatorLogic($value);
            } else {
                $this->testRestults[$key] = false;
                $this->errorMessages[$key] = 'Bad value!';
                $this->validationMetaArray[$key] = null;
                unset($this->validationMetaArray[$key]);
            }
        }
    }

     /**
     * A method that removes unnecessary validation related data for
     * input fields that were not submitted.
     * 
     * @param array $phpFVA The PHP Filter Validation array (Phase 1).
     * @param array $phpFEMA The PHP Filter Validation error messages.
     * @param array $validationMA The PHP validation meta array for phase 2.
     */ 
    private function pruneValidator(array &$phpFVA, array &$phpFEMA, array &$validationMA)
    {
        foreach (array_keys($phpFVA) as $key) {
            if (!isset($this->filteredInputArray[$key])) {
                unset($phpFVA[$key], $phpFEMA[$key], $validationMA[$key]);
            }
        }
    }

    /**
     * 
     * @param array $targetArray
     * @param array $targetElements
     * @return array
     */
    protected function extractFilteredElements(array &$targetArray, array $targetElements): array
    {
        $basket = [];

        foreach ($targetElements as $key) {    
            if (isset($targetArray[$key])) {
                $basket[$key] = $targetArray[$key];
                
                // Free up resources.
                $targetArray[$key] = null;
                unset($targetArray[$key]);
            }
        }

        return $basket;
    }

     /**
     * A method that gets test results and error messages from
     * field / task specific a Validator
     * 
     * @param Validator $validator An object of type Validator.
     * @param array $targetElements The elements / fields for which you want test results and error messages.
     */ 
    protected function mergeValidatorTestResultsAndMessages(Validator $validator, array $targetElements)
    {
        $testResults = $validator->getTestResults();
        $errorMessages = $validator->getErrorMessages();

        foreach (array_keys($targetElements) as $key) {
            $this->testRestults[$key] = $testResults[$key];
            $this->errorMessages[$key] = $errorMessages[$key];
        }
    }

    /**
     * Lost method. Remove. Refactor. No longer relevant? Terrible name, too.
     * This was a fix to the problem that PHP does not accept string integers
     * as indexes, even on associative arrays (it converts them to integers).
     */
    protected function programValidator()
    {
        isset($this->filteredInputArray[' 0']) ? $this->setIndexedPHPFilterInstructions() : $this->setNamedPHPFilterInstructions();
    }

    /**
     * A method that converts PHP filter validation errors (phase 1) into text error
     * messages, and consolidates test results into a separate array.
     * 
     * @param array $phpFilterResults Phase 1 validation results using PHP filter validate filters.
     * @param array $phpFilterErrMsgs A set of pre-defined error messages found in Validation sub-class constructors.
     * @param array $errors The errors that have occurred during validation.
     * @param array $testResults The set of boolean true / false values derived from $phpFilterResults.
     */
    protected function phpFilterErrToMesg(array $phpFilterResults, array $phpFilterErrMsgs, array &$errors,  array &$testResults)
    {
        foreach ($phpFilterResults as $key => $value) {
            if ($this->identical($value, false) || $this->identical($value, null)) {
                $testResults[$key] = false;
                $errors[$key] = $phpFilterErrMsgs[$key];   // Here's where the error message is transfered.
            } else {
                $testResults[$key] = true;
                $errors[$key] = '';
            }
        }
    }

                /*  Universal field validator / variable functions.  */

    /**
     * 
     * @param string $value
     * @param array $validationMetaArray
     * @param type $errorMessage
     * @return boolean
     * @throws SecurityException
     */
    protected function ajaxFlag(string $value, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  // $optional, $kind, $type, $min, $regex,  $noEmptyString, $specificValue, $rangeOfValues

        if ($this->validateInput($value, $optional, $kind, $type, $min, $max, $regex, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage)) {
            return true;
        }

        throw new SecurityException("An invalid XMLHttpRequest was attempted.");
    }

    /**
     * 
     * @param string $value
     * @param array $validationMetaArray
     * @param type $errorMessage
     * @return boolean
     * @throws SecurityException
     */
    protected function nocache(string $value, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  // $potional, $kind, $type, $min, $regex,  $noEmptyString, $specificValue, $rangeOfValues

        if ($this->validateInput($value, $optional, $kind, $type, $min, $max, $regex, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage)) {
            return true;
        }

        throw new SecurityException("An invalid HTTP request was attempted.");
    }
}
?>