<?php
namespace Isaac\Sanitizers;

use Isaac\Filters\Filter as Filter;
use Isaac\PreValidators\PreValidator as PreValidator;

/**
 * Description of FilesSanitizer
 */
abstract class FilesSanitizer extends Sanitizer
{
    /* Properties */
    private $fileNameCallback    = '';
    private $htmlControlCallback = '';

    /* Constructor */
    public function __construct(PreValidator $preValidator, Filter $filter, array $phpFFA) 
    {
        parent::__construct($preValidator, $filter, $phpFFA);
    }
    
    public function sanitizeFileNames($inputArray)
    {
        foreach ($inputArray as $control => &$files) {
            for ($i = 0, $length = count($files); $i < $length; ++$i) {
               $files[$i]['name'] = $this->filter->scrub($files[$i]['name']);
            }
        }
    }

    protected function setUserDefinedFilterArray()
    {
        $this->userDefinedFilterArray = [
            'name'     => ['filter'  => FILTER_CALLBACK,
                           'flags'   => FILTER_REQUIRE_SCALAR,
                           'options' => [$this->filter, $this->fileNameCallback]],
            'type'     => ['filter'  => FILTER_CALLBACK,
                           'flags'   => FILTER_REQUIRE_SCALAR,
                           'options' => [$this->filter, $this->htmlControlCallback]],
            'size'     => ['filter'  => FILTER_SANITIZE_NUMBER_INT,
                           'flags'   => FILTER_REQUIRE_SCALAR],
            'tmp_name' => ['filter'  => FILTER_SANITIZE_STRING,
                           'flags'   => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
            'error'    => ['filter'  => FILTER_SANITIZE_NUMBER_INT,
                           'flags'   => FILTER_REQUIRE_SCALAR]
        ];
        
        return;
    }
    
    protected function setPhpStringFilterArray()
    {
        $this->phpStringFilterArray = [
            'name'     => ['filter' => FILTER_SANITIZE_STRING,
                           'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
            'type'     => ['filter' => FILTER_SANITIZE_STRING,
                           'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
            'size'     => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                           'flags'  => FILTER_REQUIRE_SCALAR],
            'tmp_name' => ['filter' => FILTER_SANITIZE_STRING,
                           'flags'  => FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH],
            'error'    => ['filter' => FILTER_SANITIZE_NUMBER_INT,
                           'flags'  => FILTER_REQUIRE_SCALAR]
        ];

        return;
    }
    
    private function isValidFilterArrayResult($phase, $control, $fileNum, array $results)
    {
        $errorKeys = [];
        
        foreach ($results as $key => $value) {
            if (($value === false) || ($value === null)) {
                $errorKeys[$key] = $key;
            }
        }

        //Process $errorKeys array and make a string, if necessary.
        if (!empty($errorKeys)) {
            $errors = implode(' ', $errorKeys);
            throw new SanitizationException("Filter failed on phase {$phase}. HTML file input named {$control}. File number {$fileNum}. " .$errors. ' file field(s).');
        }
        
        return;
    }
    
    protected function sanitizeInputSource()
    {
        $error1 = 'User defined filter error at phase (';
        $error2 = 'PHP string filter error at phase (';
        $error3 = 'PHP field filter error at phase (';

        foreach ($this->inputArray as $control => $files) {
            for ($i = 0, $length = count($files); $i < $length; ++$i) {
                $file = $files[$i];
                $filterPhase = '';
                $int = 1;
                
                $filterPhase = $error1 .$int. ')';
                $results = filter_var_array($file, $this->userDefinedFilterArray);
                $this->isValidFilterArrayResult($filterPhase, $control, $i, $results);

                $filterPhase = $error2 .++$int. ')';
                $results = filter_var_array($results, $this->phpStringFilterArray);
                $this->isValidFilterArrayResult($filterPhase, $control, $i, $results);

                $filterPhase = $error3 .++$int. ')';
                $results = filter_var_array($results, $this->phpFieldFilterArray);
                $this->isValidFilterArrayResult($filterPhase, $control, $i, $results);
                
                //Finished. No errors found if this statement is reached.
                $this->filteredInputArray[$control][$i] = $results;
            }
        }

        return;
    }
    
    public function sanitize()
    {
        if ($this->isGoodCallback($this->filter, $this->fileNameCallback) &&
                $this->isGoodCallback($this->filter, $this->htmlControlCallback)) {
            $this->sanitizeInputSource(); //Does the main sanitization work in each sub-class.
        }
        
        return;
    }
}
