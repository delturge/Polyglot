<?php
declare(strict_types=1);

namespace tfwd\Sanitizers;

use tfwd\Framework\Base as Base;
use tfwd\Filters\Filter as Filter;
use tfwd\Exceptions\SanitizationException as SanitizationException;
use tfwd\Exceptions\SecurityException as SecurityException;
use tfwd\Sanitizers\Sanitizer as Sanitizer;

/**
 * A class responsible for managing the filtering of
 * dirty / raw input data.
 * 
 * @author Anthony E. Rutledge
 * @version 10-10-2018
 * @copyright (c) 2018, Time Flies Web Design
 */
abstract class Sanitizer extends Base
{
    /**
     * A the name of the input source that requires filtering.
     * 
     * @link http://php.net/manual/en/language.variables.superglobals.php
     * @var string
     */
    private $inputSource;

    /**
     * All of the values that need to be sanitized.
     * @var array
     */
    protected $inputData;

    /**
     * Holds user defined UTF-8 filter instructions for each value.
     * @var array
     */
    protected $userDefinedUtf8Filters = [];

    /**
     * Holds user defined ISO-8859-1 filter instructions for each value.
     * @var array
     */
    protected $userDefinedIso88591Filters = [];

    /**
     * Holds official PHP string filter instructions for each value.
     * @link http://php.net/manual/en/filter.filters.sanitize.php
     * 
     * @var array 
     */
    protected $phpStringFilters = [];

    /**
     * Holds official PHP field type filter instructions for each value.
     * @link http://php.net/manual/en/filter.filters.sanitize.php
     * 
     * @var array 
     */
    protected $phpFieldFilters;

    /**
     * Holds filtered data. The eventual output.
     * @link http://php.net/manual/en/filter.filters.sanitize.php
     * 
     * @var array
     */
    protected $filteredData = [];

    //Callback related
    
    /**
     * The object used in a callback during phase 1 user-defined filtering:
     * @link http://php.net/manual/en/language.types.callable.php
     * @link http://php.net/manual/en/filter.filters.misc.php
     * @var Filter
     */
    protected $utf8Filter;

    /**
     * The object used in a callback during phase 2 user-defined filtering:
     * @link http://php.net/manual/en/language.types.callable.php
     * @link http://php.net/manual/en/filter.filters.misc.php
     * @var Filter
     */
    protected $iso88591Filter;

    /**
     * The method used in callback object during user-defined filtering:
     * @link http://php.net/manual/en/language.types.callable.php
     * @link http://php.net/manual/en/filter.filters.misc.php0
     * @var string
     */
    private $callback = 'scrub';

    /**
     * A constructor that initializes a Sanitizer object.
     * 
     * @param string $inputSource The name of the input source: $_POST, $_GET, $_FILES, ...
     * @param array $inputData The data that requires filtering!
     * @param Filter An object that contains a callback filter method (scrub()) for user-defined filters.
     * @param Filter An object that contains a callback filter method (scrub()) for user-defined filters.
     * @param array $phpFFA PHP Field Filter Array. A grouping of PHP field specific filters.
     */
    protected function __construct(string $inputSource, array $inputData, array $phpFFA, Filter $utf8Filter, Filter $iso88591Filter) 
    {
        $this->inputSource = $inputSource;
        $this->inputData = $inputData;
        $this->utf8Filter = $utf8Filter;
        $this->iso88591Filter = $iso88591Filter;
        $this->setPhpFieldFilters($phpFFA);
        $this->prunePhpFieldFilters();
        $this->setUserDefinedUtf8Filters();
        $this->setUserDefinedIso88591Filters();
        $this->setPhpStringFilters();
    }

    /**
     * A method that attempts to help with clearing of memory.
     * May remove in the future.
     */
    public function __destruct() 
    {
        //Destructor work.
        $this->userDefinedFilters = null;
        $this->phpFieldFilters = null;
        $this->phpStringFilters = null;
        $this->filteredData = null;
        unset($this->userDefinedFilters, $this->phpFieldFilters, $this->phpStringFilters, $this->filteredData);
    }

    /**
     * A method that set an instance of a class to
     * a static variable. Why? To avoid putting
     * variables in the global namespace (index.php).
     * 
     * @param Sanitizer $sanitizer
     */
    public static function setInstance(Sanitizer $sanitizer)
    {
        self::$instance = $sanitizer;
    }

     /**
     * A method that returns filtered data.
     * 
     * @return array Filtered input data.
     */
    public function getFilteredData(): array
    {
        return $this->filteredData;
    }

    /**
     * Mutators that sets the PHP field filters array.
     * 
     * @param $phpFFA array The data needing to be filtered.
     * @throws LengthException 
     */
    private function setPhpFieldFilters(array $phpFFA)
    {
        if (empty($phpFFA)) {
            throw new \LengthException("The PHP Field Filter Array (phpFFA) for cannot be empty.");
        }

        $this->phpFieldFilters = $phpFFA;
    }

    /**
     * A method that removes filters for HTML controls that are not successful.
     * 
     * This method makes it easier to work with unsuccessful checkbox controls and more.
     * Why? Unsuccessful checkbox controls do not register in $_POST or $_GET.
     * 
     * @link https://www.w3.org/TR/html401/interact/forms.html#h-17.13.2
     * @link https://www.w3.org/TR/html5/sec-forms.html#forms-form-submission
     */
    private function prunePhpFieldFilters()
    {
        foreach (array_keys($this->phpFieldFilters) as $control) {
            if (!isset($this->inputData[$control])) {       // If the transitory HTML control is not successful / present.
                unset($this->phpFieldFilters[$control]);    // Remove the sanitizer instructions for it.
            }
        }
    }

    /**
     * A method that sets up user-defined UTF-8 filters.
     * 
     * @link http://php.net/manual/en/language.types.callable.php
     * @link http://php.net/manual/en/filter.filters.misc.php0
     */
    private function setUserDefinedUtf8Filters()
    {
        foreach ($this->phpFieldFilters as $key => $value) {            
            if (!is_array($value)) {
                $this->userDefinedUtf8Filters[$key] = [
                    'filter'  => FILTER_CALLBACK,
                    'flags'   => FILTER_REQUIRE_SCALAR,
                    'options' => [$this->utf8Filter, $this->callback]
                ];
            } else {
                $this->userDefinedUtf8Filters[$key] = [
                    'filter'  => FILTER_CALLBACK,
                    'flags'   => FILTER_REQUIRE_ARRAY,
                    'options' => [$this->utf8Filter, $this->callback]
                ];
            }
        }
    }

    /**
     * A method that sets up user-defined IS0-8859-1 filters.
     * 
     * @link http://php.net/manual/en/language.types.callable.php
     * @link http://php.net/manual/en/filter.filters.misc.php0
     */
    private function setUserDefinedIso88591Filters()
    {
        foreach ($this->phpFieldFilters as $key => $value) {            
            if (!is_array($value)) {
                $this->userDefinedIso88591Filters[$key] = [
                    'filter'  => FILTER_CALLBACK,
                    'flags'   => FILTER_REQUIRE_SCALAR,
                    'options' => [$this->iso88591Filter, $this->callback]
                ];
            } else {
                $this->userDefinedIso88591Filters[$key] = [
                    'filter'  => FILTER_CALLBACK,
                    'flags'   => FILTER_REQUIRE_ARRAY,
                    'options' => [$this->iso88591Filter, $this->callback]
                ];
            }
        }
    }

    /**
     * A method that sets up PHP string filters
     * based on the actual controllers have been
     * 
     * @link http://php.net/manual/en/filter.filters.sanitize.php
     */
    private function setPhpStringFilters()
    {
        foreach ($this->phpFieldFilters as $key => $value) {
            if (!is_array($value)) {
                $this->phpStringFilters[$key] = [
                    'filter' => FILTER_SANITIZE_STRING,
                    'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
                ];
            } else {
                $this->phpStringFilters[$key] = [
                    'filter' => FILTER_SANITIZE_STRING,
                    'flags'  => FILTER_REQUIRE_ARRAY | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
                ];
            }
        }
    }

    /**
     * A method that makes a report string when errors are detected.
     * 
     * @param array $results
     * @param array $errorKeys
     * @param string $error
     * 
     * @return string $error
     */
    private function makeErrorReport(array $results, array $errorKeys, string $error = ''): string
    {
        foreach ($results as $key => $value) {
            if (!is_array($value)) {
                if (isset($errorKeys[$key])) {
                    $error .= $errorKeys[$key] . ' ' ;
                }
            } else {
                $error .= $this->makeErrorReport($value, $errorKeys[$key], $error);
            }
        }

        return $error;
    }

    /**
     * A method that determines name (key) of the fields
     * that failed their sanitization process.
     * 
     * @param array $results The results of the a filter phase.
     * @retun array $errorKeys An array of failed field names, or an empty array.
     */
    private function testFilterResults(array $results): array
    {
        $errorKeys = [];

        foreach ($results as $key => $value) {
            if (!is_array($value)) {
                if (($value === false) || ($value === null)) {
                    $errorKeys[$key] = $key;
                }
            } else {
                $errorKeys[$key] = $this->testFilterResults($value);
            }
        }

        return $errorKeys;
    }

    /**
     * A method that returns the phase error message, if necessary.
     * 
     * @param int $phase The current filter phase (1, 2, 3, 4, ...)
     * @return string $filterResults The results from the filter that was just applied to data.
     */
    private function getPhaseErrorMessage(int $phase): string
    {
        switch ($phase) {
            case 1:
                return 'User defined UTF-8 filter error.';
            case 2:
                return 'User defined ISO-8859-1 filter error.';
            case 3:
                return 'PHP string filter error.';
            case 4:
                return 'PHP field filter error.';
            default:
                return 'Fatal filter error.';
        }
    }

    /**
     * A method that makes a report string when errors are detected.
     * 
     * @param int $phase The current filter phase (1, 2, or 3)
     * @param array $filterResults The results from the filter that was just applied to data.
     * 
     * @throws RuntimeException
     * @throws SanitizerException
     */
    private function isValidFilterResult(int $phase, array $filterResults)
    {
        $message;

        // Pre-liminary test.
        if (empty($filterResults)) {
            $message = $this->getPhaseErrorMessage($phase);
            throw new \RuntimeException("{$message}\nEmpty filter result set at phase {$phase}.");
        }

        // Main test.
        $errorKeys = $this->testFilterResults($filterResults);

        // Final Evaluation.
        if (!empty($errorKeys)) {
            $message = $this->getPhaseErrorMessage($phase);
            $report = $this->makeErrorReport($filterResults, $errorKeys);
            throw new SanitizationException("{$message}\nFilter failure on the following elements {$report} @ phase {$phase}.");
        }
    }

    /**
     * A method that implements a four phase input filter.
     * 
     * Pass 1: User Defined UTF-8 Filter via callback (iterative).
     * Pass 2: User Defined ISO-8859-1 Filter via callback (iterative).
     * Pass 3: PHP defined string filters.
     * Pass 4: PHP defined field specific filters.
     * 
     * @link http://php.net/manual/en/filter.filters.sanitize.php
     * @link http://php.net/manual/en/function.filter-var-array.php
     * 
     * @throws SecurityException
     */
    private function sanitizeInputData()
    {
        $filterResults;
        $phase = 1;

        // Phase 1 (iterative)
        $filterResults = filter_var_array($this->inputData, $this->userDefinedUtf8Filters);   // User Defined filtering for input
        $this->isValidFilterResult($phase, $filterResults);

        // Phase 2 (iterative)
        $phase++;
        $filterResults = filter_var_array($filterResults, $this->userDefinedIso88591Filters); // User Defined filtering for input
        $this->isValidFilterResult($phase, $filterResults);

        // Phase 3
        $phase++;
        $filterResults = filter_var_array($filterResults, $this->phpStringFilters);           // PHP string filtering for input.
        $this->isValidFilterResult($phase, $filterResults);

        // Phase 4
        $phase++;
        $filterResults = filter_var_array($filterResults, $this->phpFieldFilters);            // PHP field specific filtering for input.
        $this->isValidFilterResult($phase, $filterResults);

        // No errors were found if this statement is reached.
        // Otherwise SecurityException would have been thrown.
        
        $this->filteredData = $filterResults;
        return true;
    }

    /**
     * A method that kicks off the sanitization process,
     * but also checks to make sure user defined filter phases 1 and 2
     * are pointed towards callable methods of an object instance.
     * 
     * @return bool true
     * @throws SanitizationException
     * @throws SecurityException
     */
    public function clean()
    {
        if ($this->isGoodCallback($this->utf8Filter, $this->callback) &&
                $this->isGoodCallback($this->iso88591Filter, $this->callback)) {
            return $this->sanitizeInputData(); // Throws fatal exceptions.
        }
        
        throw new SanitizationException("Unable to filter the input data.");
    }
}
?>