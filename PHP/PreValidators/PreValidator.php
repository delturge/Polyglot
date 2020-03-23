<?php
declare(strict_types=1);

namespace tfwd\PreValidators;

use tfwd\Framework\Base as Base;
use tfwd\Encoders\Encoder as Encoder;
use tfwd\Exceptions\SecurityException as SecurityException;
use tfwd\Exceptions\PreValidatorException as PreValidatorException;

/**
 * A class responsible for managing the preliminary validation of
 * dirty / raw input data. These test are simple and should occur
 * before and deep examinations (filtering, final validation) of raw data occurs.
 * 
 * Note: Base is the framework base class.
 * 
 * @author Anthony E. Rutledge
 * @version 10-11-2018
 * @copyright (c) 2018, Time Flies Web Design
 */
abstract class PreValidator extends Base
{
    /**
     * The name of the source of the raw data.
     * 
     * @var string
     */
    private $inputSource;
    
    /**
     * The minimum number of elements allowed to be submitted.
     * 
     * @var int 
     */
    private $minElements;
    
    /**
     * The maximum number of elements allowed to be submitted.
     * 
     * @var int 
     */
    private $maxElements;
    
    /**
     * The actual number of elements that require pre-validation.
     * 
     * @var int 
     */
    private $numElements;

    /**
     * Dirty and unsafe data, directly injected from the input source.
     * 
     * @var array 
     */
    private $rawData;
    
    /**
     * The maximum characters allowed for each HTML control.
     * 
     * @var array 
     */
    private $rawDataMeta;

    /**
     * Any inputs / controls that are not always "successful / present"
     * upon form submission.
     * 
     * @var array 
     */
    private $transientInputs;

    /**
     * The allowable sources of input data. Add more as needed.
     * Just make up a name and stick with it, if necessary.
     * 
     * @var array
     */
    private $inputSources = [
        '$_SERVER' => true,
        '$_POST' => true,
        '$_GET' => true,
        '$_COOKIE' => true,
        '$_FILES' => true,
        '$_SESSION' => true,
        '$_DATABASE' => true, // Imaginary
        'FILE' => true // Imaginary
    ]; 

    /**
     * Used to test and, if necessary, convert strings to a given character encoding.
     * 
     * @var Encoder
     */
    private $encoder;

    /**
     * A constructor that initializes a PreValidator object.
     * 
     * @param string $inputSource The name of the input source: $_POST, $_GET, $_FILES, ...
     * @param array $rawData The data that requires pre-validating!
     * @param int $minElements The minimum number of elements allowed to be submitted.
     * @param int $maxElements The maximum number of elements allowed to be submitted.
     * @param array $rawDataMeta An object that contains a callback filter method (scrub()) for user-defined filters.
     * @param array $transientInputs An object that contains a callback filter method (scrub()) for user-defined filters.
     * @param Encoder $encoder PHP Field Filter Array. A grouping of PHP field specific filters.
     */
    public function __construct(string $inputSource, array $rawData, int $minElements, int $maxElements, array $rawDataMeta, array $transientInputs, Encoder $encoder) 
    {
        $this->setInputSource($inputSource);
        $this->setRawData($rawData);
        $this->setMinElements($minElements);
        $this->setMaxElements($maxElements);
        $this->compareMinMaxElements();
        $this->setNumElements($this->countInputs());
        $this->setInputMetaArray($rawDataMeta);
        $this->setTransientInputs($transientInputs);
        $this->pruneInputMetaArray();
        $this->encoder = $encoder;
    }
    
     /**
     * A method that returns the name of the input source.
     *
     * @retuns string 
     */
    public function getInputSource()
    {
        return $this->inputSource;
    }

     /**
     * A method that returns the raw data.
     *
     * @retuns array 
     */
    public function getRawData()
    {
        return $this->rawData;
    }

     /**
     * A method that sets the name of the input source.
     * 
     * @param string $inputSource
     * @throws UnexpectedValueException 
     */
    private function setInputSource(string $inputSource)
    {
        if (!isset($this->inputSources[$inputSource])) {
            throw new \UnexpectedValueException("The input argument, {$inputSource}, must be a valid input source.");
        }

        $this->inputSource = $inputSource;
    }

     /**
     * A method that sets the target, raw data.
     * 
     * @param array $rawData
     * @throws LengthException 
     */
    private function setRawData(array $rawData)
    {
        if (empty($rawData)) {
            throw new \LengthException("The input array for {$this->inputSource} is empty.");
        }

        $this->rawData = $rawData;
    }

     /**
     * A method that sets minimum number of elements allowed.
     * 
     * @param int $number
     * @throws DomainException 
     */
    private function setMinElements(int $number)
    {
        if (!$this->isPosInt($number)) {
            throw new \DomainException("The minimum number of elements to pre-validate must be a value greater than zero (0). $number provided.");
        }

        $this->minElements = $number;
    }

     /**
     * A method that sets maximum number of elements allowed.
     * 
     * @param int $number
     * @throws DomainException 
     */
    private function setMaxElements(int $number)
    {
        if (!$this->isPosInt($number)) {
            throw new \DomainException("The maximum number of elements to pre-validate must be a value greater than zero (0). $number provided.");
        }

        $this->maxElements = $number;
    }

     /**
     * A method that sets the actual number of elements to be pre-validated.
     * 
     * @param int $number
     * @throws DomainException 
     */
    private function setNumElements(int $number)
    {
        if (!$this->isPosInt($number)) {
            throw new \DomainException("The number of filter elements must be a greater than zero(0). $number given");
        }

        $this->numElements = $number;
    }

     /**
     * A method that sets the raw data meta array.
     * This array contains data about the rawData.
     * 
     * @param array $rawDataMeta
     * @throws LengthException 
     */
    private function setInputMetaArray(array $rawDataMeta)
    {
        if (empty($rawDataMeta)) {
            throw new \LengthException('The input meta array for a PreValidator cannot be empty.');
        }
        
        $this->rawDataMeta = $rawDataMeta;
    }

     /**
     * A method that sets an array containing the names of inputs / controls
     * that may not be present / successful (example: checkboxes).
     * 
     * @param array $transientInputs
     * @throws UnexpectedValueException 
     */
    private function setTransientInputs(array $transientInputs)
    {
        if (!isset($transientInputs)) {
            throw new \UnexpectedValueException('The input meta array for a PreValidator cannot be null.');
        }

        $this->transientInputs = $transientInputs;
    }

    /**
     * This method makes it easier to work with unsuccessful checkbox controls and more.
     * Why? Unsuccessful checkbox controls do not register in $_POST or $_GET.
     * Note: May be subject to being overridden in child classes as necessary.
     */
    protected function pruneInputMetaArray()
    {
        $length = count($this->transientInputs);

        for ($i = 0; $i < $length; $i++) {
            $transitoryInput = $this->transitoryInputs[$i];

            if (!isset($this->rawData[$transitoryInput])) {     // If the transitory HTML control is not successful / present.
                unset($this->inputMetaArray[$transitoryInput]); // Remove pre-validation instructions for it!
            }
        }
    }

    /**
     * A method that checks to see if the minimum number of elements is greater
     * than the maximum number of elements.
     * 
     * @throws DomainException
     */
    private function compareMinMaxElements()
    {
        if ($this->minElements > $this->maxElements) {
            throw new \DomainException("\$minElements ({$this->minElements}), must be less than or equal (<=) to \$maxElements ({$this->maxElements})");
        }
    }

    /**
     * A method that determines the number of elements that need pre-validation.
     * 
     * @return int
     */
    private function countInputs()
    {
        return count($this->rawData, COUNT_RECURSIVE);
    }

    /**
     * The 1st Horseman of pre-validation.
     * A method that tests if the minimum number of elements exists.
     * 
     * @return bool true
     * @throws RangeException
     */
    protected function hasMinNumElements()
    {
        if ($this->numElements < $this->minElements) {
            throw new \RangeException("{$this->inputSource} requires at least {$this->minElements} elements. Not enough ({$this->numElements}) submitted.");
        }

        return true;
    }

    /**
     * The 2nd Horseman of pre-validation.
     * A method that tests if the actual number of input elements exceeds
     * the maximum number allowed.
     * 
     * @return bool true
     * @throws RangeException
     */
    protected function hasMaxOrLessNumElements()
    {
        if ($this->numElements > $this->maxElements) {
            throw new \RangeException("{$this->inputSource} cannot exceed {$this->maxElements} elements. Too many ({$this->numFilterElements}) submitted.");
        }

        return true;
    }

    /**
     * A method that checks that required elements are present using iteration
     * and recursion.
     * 
     * @param array $requiredElements
     * @param array $inputData
     * @return bool true
     * @throws OutOfBoundsException
     */
    private function checkForElements(array $requiredElements, array $inputData)
    {
        foreach ($requiredElements as $controlName => $charLimit) {
            if (!is_array($charLimit)) {
                if (!isset($inputData[$controlName])) {
                    throw new OutOfBoundsException("The index {$controlName} does not exist in {$this->inputSource}!");
                }
            } else {
                $this->checkForElements($charLimit, $inputData[$controlName]);
            }
        }

        return true;
    }

    /**
     * The 3rd Horseman of pre-validation.
     * A method that tests if required elements are present.
     * 
     * @return bool true
     */
    private function hasRequiredElements()
    {
        return $this->checkForElements($this->rawDataMeta, $this->rawData);
    }

    /**
     * A method that delegates the task of checking, and potentially encoding
     * data, to an Encoder object.
     * 
     * @return bool true
     */
    private function checkEncoding()
    {
        $this->encoder->encodeArray($this->rawData); // Throws fatal exceptions.
        return true;
    }

    /**
     * The 4th Horseman of pre-validation.
     * A method that test the encoding of input.
     * 
     * @return bool true
     */
    protected function hasGoodEncoding()
    {
        return $this->checkEncoding();
    }

    /**
     * A method that tests string lengths.
     * 
     * @param array $requiredElements
     * @param array $inputData
     * @return boolean true
     * @throws UnexpectedValueException
     * @throws LengthException
     */
    private function hasGoodStringLengths(array $requiredElements, array $inputData)
    {
        foreach ($requiredElements as $controlName => $limit) {
            if (!is_array($limit)) {
                $length = mb_strlen($this->rawData[$controlName], 'UTF-8');
                
                if ($length === false) {
                    throw new \UnexpectedValueException("The value for {$controlName} is not UTF-8 encoded.");
                }

                if ($length > $limit) {
                    throw new \LengthException("The length of *** {$controlName} *** is too long!");
                }
            } else {
               $this->hasGoodStringLengths($limit, $inputData[$controlName]);
            }
        }

        return true;
    }

    /**
     * The 5th Horseman of pre-validation.
     * A method that tests the size of input elements. This could mean string
     * lengths or file sizes, depending on the concrete children of this super class.
     * 
     * @return bool
     */
    protected function hasGoodSizedElements()
    {
        return $this->hasGoodStringLengths($this->rawMetaData, $this->rawData);
    }

    /**
     * Calls the "Five Horsemen" of pre-validation.
     * A method that manages the pre-validation of raw input data.
     * All test must pass for the program to continue.
     * 
     * @return boolean true
     * @throws SecurityException
     */
    public function test()
    {
        if ($this->hasMinNumElements() &&
                $this->hasMaxOrLessNumElements() &&
                $this->hasRequiredElements() &&
                $this->hasGoodEncoding() &&  // Deal with encoding before dealing with strings directly.
                $this->hasGoodSizedElements()) {
            return true;
        }

        throw new PreValidatorException("The raw data is malformed at the pre-validation stage.");
    }
}
?>