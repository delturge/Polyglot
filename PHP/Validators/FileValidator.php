<?php
namespace Isaac\Validators;

abstract class FileValidator extends Validator
{
    /* Properties */

    //Array
    protected $fileProps = null;

    private $fileUploadErrors = [
        0 => 'Upload complete!',                //There is no error, the file uploaded with success.',
        1 => 'Upload exceeds max file size.',   //The uploaded file exceeds the upload_max_filesize directive in php.ini!',
        2 => 'File exceeds max file size.',     //The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form!',
        3 => 'Upload failure! Partial upload.', //The uploaded file was only partially uploaded!',
        4 => 'No file submitted.',              //No file was uploaded!',
        6 => 'Server error 1.',                 //Missing a temporary folder for file uploads!',
        7 => 'Server error 2.',                 //Failed to write file to disk!',
        8 => 'Upload canceled.'                 //A PHP extension stopped the file upload.',
    ];

    //Blacklisting Regular Expression.
    private $nameRegex    = '/(?>\A[0-9A-Za-z-_]{1,250}?\.[A-Za-z]{1,4}\z){1}?/u';
    private $typeRegex    = '/(?>\A[a-z]{1,20}\/[0-9A-Za-z.-]{1,50}\z){1}?/u';
    private $tmpNameRegex = '/(?>\A[\/\\A-Za-z:_-]{2,255}\z){1}?/u';

    /* Constructor */
    public function __construct(array $phpFVA, array $phpFEMA, array $validationMA, array $filteredInputArray, array $validatorTargets, array $fileProps)
    {
        parent::__construct($phpFVA, $phpFEMA, $validationMA, $filteredInputArray, $validatorTargets);
        $this->fileProps = $fileProps;
    }
    
    /* Validators*/

    /* Mutators */
    protected function setPHPFieldValidationArray(array $filteredInputArray, array $fileProps, array &$phpFVA)
    {
        foreach ($filteredInputArray as $control => $files) {
            for ($i = 0, $length = count($files); $i < $length; ++$i) {
                $phpFVA[$control][$i]['name'] = [
                    'filter'  => FILTER_VALIDATE_REGEXP, 
                    'flags'   => FILTER_REQUIRE_SCALAR, 
                    'options' => ['regexp' => $this->nameRegex]
                ];

                $phpFVA[$control][$i]['type'] = [
                    'filter'  => FILTER_VALIDATE_REGEXP, 
                    'flags'   => FILTER_REQUIRE_SCALAR, 
                    'options' => ['regexp' => $this->typeRegex]
                ];

                $phpFVA[$control][$i]['size']     = [
                    'filter'  => FILTER_VALIDATE_INT,
                    'flags'   => FILTER_REQUIRE_SCALAR,
                    'options' => [
                        'min_range' => $fileProps[$control]['minSize'], 
                        'max_range' => $fileProps[$control]['maxSize']
                    ]
                ];

                $phpFVA[$control][$i]['tmp_name'] = [
                    'filter'  => FILTER_VALIDATE_REGEXP, 
                    'flags'   => FILTER_REQUIRE_SCALAR, 
                    'options' => ['regexp' => $this->tmpNameRegex]
                ];

                $phpFVA[$control][$i]['error']    = [
                    'filter'  => FILTER_VALIDATE_INT,
                    'flags'   => FILTER_REQUIRE_SCALAR,
                    'options' => ['min_range' => 0, 'max_range' => 0]
                ];
            }
        }
    }

    protected function pruneValidator(array &$phpFVA, array &$validationMA)
    {
        foreach (array_keys($phpFVA) as $key) {
            if (!isset($this->filteredInputArray[$key])) {
                unset($phpFVA[$key], $validationMA[$key]);
            }
        }
        
        return;
    }
    
    protected function isVoidInput()
    {
        return;
    }

    protected function translateValidatedInput() //Creates an associative array, two elements per file.
    {
        $this->translatedInputArray = $this->filteredInputArray;
        return;
    }

    /*Core form input validation test methods.*/
    private function stringTest($input, $kind, $length, $min, $max, $pattern, &$errorMessage)
    {     
        //Because some fields are optional.
        if (mb_strpos($kind, 'opt') === 0) {
            if ($input === '') {
                $errorMessage = '';
                return;
            } else {
                $kind = substr($kind, 3); //Grab the string after the sub-string, opt.
            }
        }
        
        //Call ctype functions based on 'kind'
        if(((($kind === 'base64') || ($kind === 'path') || ($kind === 'file') ||($kind === 'mimeType') || ($kind === 'url') || ($kind === 'email') || ($kind === 'phone') || ($kind === 'ccode') || ($kind === 'ipAddress') || ($kind === 'captcha') || ($kind === 'state') || ($kind === 'zip')) && ctype_graph($input)) ||
            ((($kind === 'name') || ($kind === 'password') || ($kind === 'title') || ($kind === 'userAgent') || ($kind === 'text') || ($kind === 'ccode') || ($kind === 'company') || ($kind === 'address') || ($kind === 'city') || ($kind === 'country') || ($kind === 'cityState')) && ctype_print($input)) ||
            ((($kind === 'answer') || ($kind === 'digest') || ($kind === 'digest')) && ctype_graph($input) && ctype_alnum($input)) ||
            ((($kind === 'word') && ctype_graph($input) && ctype_alpha($input)) ||
            ((($kind === 'extension')) && ctype_graph($input) && ctype_digit($input))))
        {
            if ($length < $min) {
                $errorMessage = 'Too small! ('.$min.' min, ' .$length. ' given.)';
            } elseif ($length > $max) {
                $errorMessage = 'Too large! ('.$max.' max, ' .$length. ' given.)';
            } elseif (preg_match($pattern, $input) === 0) {                      //Test string's pattern with a regular expression.
                $errorMessage = 'Invalid string format!';
            } else {
                $errorMessage = '';                                          //The error message is the empty string.
            }
        } else {
            $errorMessage = 'Invalid entry!';
        }
        
        return;
    }
    
    private function integerTest($input, $int, $min, $max, $pattern, &$errorMessage)
    {
        if (is_int($int)) {                                        //Test data type.
            if ($input !== (string) $int) {                        //Convert $int into a string to see if it matches original input.
                $errorMessage = 'Invalid input! (fake value)';
            } elseif (preg_match($pattern, $input) === 0) {        //Regular Expression Test
                $errorMessage = 'Invalid input! (Bad number format)';
            } elseif ($int < $min) {                               //Test against minimum value.
                $errorMessage = 'Invalid selection! (too low)';
            } elseif ($int > $max) {                               //Test against maximum value.
                $errorMessage = 'Invalid selection! (too high)';
            } else {
                $errorMessage = '';                                //The erroor message is the empty string.
            }
        } else {
            $errorMessage = 'Invalid data type!';
        }
        
        return;
    }
    
    private function floatTest($input, $float, $min, $max, $pattern, &$errorMessage)
    {
        if (is_float($float)) {                                         //Test data type.
            if($input !== (string)$float) {                            //Convert $input into a string to see if it matches original input.
                $errorMessage = 'Invalid input! (Not an option)';
            } elseif(preg_match($pattern, $input) === 0) {             //Regular expression test.
                $errorMessage = 'Invalid input! (Bad number format)';
            } elseif($input < $min) {
                $errorMessage = 'Invalid selection! (too low)';
            } elseif($input > $max) {
                $errorMessage = 'Invalid selection! (too high)';
            } else {
                $errorMessage = '';                                    //The erroor message is the empty string.
            }
        } else {
            $errorMessage = 'Invalid data type!';
        }
        
        return;
    }
    
    private function matchingTest($input, $noEmptyString, $specificValue, $rangeOfValues, &$errorMessage)
    {        
        /*Begin comparison testing.*/
        if (($specificValue === false) && ($rangeOfValues === null) && ($input === '')) { //$input must be a non-empty string.
            if (!$noEmptyString) {
                return true;
            } else {
               $errorMessage = 'Must be filled in!';
            }
        } elseif (($specificValue === false) && ($rangeOfValues === null) && ($input !== '')) {   
            return true;
        } elseif ((is_string($specificValue) || is_int($specificValue) || is_float($specificValue)) && ($rangeOfValues === null)) {
            if ($input === $specificValue) { //Input must strictly match a specific value.
                return true;
            }
            
            $errorMessage = 'Bad match.';
        } elseif (($specificValue === false) && is_array($rangeOfValues) && !empty($rangeOfValues)) {  //Input must be one in a range of values.
            if(in_array($input, $rangeOfValues, true)) {//Input must strictly match a value in the array.
                return true;
            }
            
            $errorMessage = 'Invalid option!';
        } else {
            $errorMessage = 'Invalid input!';
        }
        
        return false;
    }
    
    private function validateInput(&$input, $kind, $type, $min, $max, $pattern, $noEmptyString = true, $specificValue = false, array $rangeOfValues = null, &$errorMessage = null)
    {
        $tempVar = null;
        $length  = strlen($input);
        
        if (($type === 'string') && is_string($input)) {   
            $tempVar = $input;
            $this->stringTest($input, $kind, $length, $min, $max, $pattern, $errorMessage);
        } elseif (($type === 'int') && ctype_graph($input) && ctype_digit($input) && is_int((int) $input)) { //Remember, true integers include negative numbers!!
            $tempVar = $this->getInt($input);
            $this->integerTest($input, $tempVar, $min, $max, $pattern, $errorMessage);
        } elseif (($type === 'float') && ctype_graph($input) && is_numeric($input) && is_float((float) $input)) { //I need to add a function that looks for one and only one decimal point, splits on that decimail point, and makes sure the parts are composed of integers.
            $tempVar = $this->getFloat($input);
            $this->floatTest($input, $tempVar, $min, $max, $pattern, $errorMessage);
        } elseif ($type === 'number') {    //This tests an actual integer or float datatype.
            $tempVar = $input;
            
            if ( is_int($input)) {
                $this->integerTest((string) $input, $tempVar, $min, $max, $errorMessage);
            } elseif (is_float($input)) {
                $this->floatTest((string) $input, $tempVar, $min, $max, $errorMessage);
            } else {
                $errorMessage = 'Invalid data entered!';
            }
        } else {
            $errorMessage = 'Invalid data entered!';
        }

        return (($errorMessage === '') && ($this->matchingTest($tempVar, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage)));
    }
    
    private function name($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }

    private function type($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }

    private function size($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues
        return $this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage);
    }

    private function isMimeType($tmpFile, $file)
    {
        //Note: Put a small MS Word document in the uploads folder and use it
        //      as a reference for determining mime type.

        /**********************************/

        $finfo = new finfo(FILEINFO_MIME_TYPE); //This should be injected into the object!!!!

        if ($finfo === false) {
            throw new UnexpectedValueException("Unable to create a FileInfo object.");
        }

        /**********************************/

        $tmpFileMimeType = $finfo->file($tmpFile);  // returns mime type
        
        if ($tmpFileMimeType === false) {
            throw new UnexpectedValueException("Unable to determine the mime type of the temporary file.");
        }

        return ($tmpFileMimeType === $this->fileProps[$file]['mimeType']);
    }

    private function tmpName($string, array $validationMetaArray, &$errorMessage, $file)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if ($this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage)) {
            if ($this->isMimeType($string, $file)) {
                return true;
            } else {
                $errorMessage = "Wrong file type!";
            }
        }

        return false;
    }

    private function error($string, array $validationMetaArray, &$errorMessage)
    {
        extract($validationMetaArray);  //$kind, $type, $min, $pattern,  $noEmptyString, $specificValue, $rangeOfValues

        if($this->validateInput($string, $kind, $type, $min, $max, $pattern, $noEmptyString, $specificValue, $rangeOfValues, $errorMessage)) {
            $errorMessage = $this->fileUploadErrors[(int) $string];  //Gives offical, PHP, reason for upload error.
            return true;
        } else {
            $errorMessage = $this->fileUploadErrors[(int) $string];  //Gives offical, PHP, reason for upload error.
        }

        return false;
    }

    private function validateFile(array $file, $fileControl, $fileNum, array &$testResults, array &$fileControlErrors)
    {
        foreach ($file as $key => $value) { //Skip the tmp_name test.
            if (!$this->identical($key, 'tmp_name')) {
                $testResults[$fileControl][$fileNum][$key] = $this->$key($value, $this->validationMetaArray[$fileControl][$key], $fileControlErrors[$fileControl][$fileNum][$key]);
            }
        }
        
        //Check the uploaded temporary file, including MIME type.
        $testResults[$fileControl][$fileNum]['tmp_name'] = $this->tmpName($file['tmp_name'], $this->validationMetaArray[$fileControl]['tmp_name'], $fileControlErrors[$fileControl][$fileNum]['tmp_name'], $file);
        return;
    }

    private function validateFilesArray(array $files, $fileControl, array &$testResults, array &$fileControlErrors)
    {
        for ($i = 0, $length = count($files); $i < $length; ++$i) {
            $this->validateFile($files[$i], $fileControl, $i, $testResults, $fileControlErrors);
        }
        
        return;
    }

    protected function coreValidatorLogic(array &$testResults, array &$fileControlErrors)
    {
        foreach ($this->filteredInputArray as $fileControl => $files) {
            if ($this->isTrue($this->testResultsArray[$fileControl])) { //Only test file controls that passed PHP Filter validation.
                $this->validateFilesArray($files, $fileControl, $testResults, $fileControlErrors);
            }
        }

        return;
    }
    
    private function pruneErrors(array &$errors)
    {
        foreach ($errors as $key => $value) {
            if ($this->identical('', $value)) {
                unset($errors[$key]);
            }
        }
        
        return;
    }
    
    private function fileControlFailed($controlName, array $errorsArray)
    {
        $this->testResultsArray[$controlName] = false;  //Indicate that the HTML file input control has failed to validate.

        for ($i = 0, $length = count($errorsArray); $i < $length; ++$i) {  // Where $length is the number of files submitted.
            $fileNum = $i + 1;
            $errors = $errorsArray[$i];
            $this->pruneErrors($errors);  //Removes any errors that might be empty strings.
            $this->errorMessagesArray[$controlName] = '';
            $this->errorMessagesArray[$controlName] .= "File {$fileNum}: " . implode(' ', $errors) . ' ';
        }

        return;
    }

    private function determineFileControlsStates(array $testResults, array $fileControlErrors)
    {
        foreach (array_keys($testResults) as $controlName) {            
            if (!isset($fileControlErrors[$controlName])) { // If there is no error message, the file control passed.
                $this->fileControlPassed($controlName);
            } else {
                $this->fileControlFailed($controlName, $fileControlErrors[$controlName]);
            }
        }
        
        return;
    }

    private function fileControlPassed($control)
    {
        $this->testResultsArray[$control]   = true;
        $this->errorMessagesArray[$control] = '';
        return;
    }
    
    private function allFileControlsPassed(array $testResults)
    {
        foreach (array_keys($testResults) as $control) {
            $this->fileControlPassed($control);
        }

        return;
    }

    private function setPHPTestResultsAndErrors(array $fileControlErrors, array $testResults)
    {
        if (empty($fileControlErrors)) { //No errors? Set all test results to true. Clear all error messages.
            $this->allFileControlsPassed($testResults);
        } else {
            $this->determineFileControlsStates($testResults, $fileControlErrors);
        }

        return;
    }
    
    private function getFileUploadErrors(array $file, $controlName, $fileNum, array &$fileControlErrors)
    {
        foreach ($file as $key => $value) {
            if ($this->identical($value, false) || $this->identical($value, null)) {
                if ($this->identical('error', $key) && $this->isNonNegInt($value)) {
                    $fileControlErrors[$controlName][$fileNum][] = $this->fileUploadErrors[$value];
                } else {
                    $fileControlErrors[$controlName][$fileNum][] = $this->phpFieldErrMsgsArray[$key];
                }
            }
        }
        
        return;
    }
    
    private function gatherFileControlErrors(array $testResults, array &$fileControlErrors)
    {
        foreach ($testResults as $controlName => $filesResults) {
            for ($i = 0, $length = count($filesResults); $i < $length; ++$i) {
                $file = $filesResults[$i];

                if (!in_array(false, $file, true) || !in_array(null, $file, true)) {
                    continue;
                }
                
                $this->getFileUploadErrors($file, $controlName, $i, $fileControlErrors);
            }
        }

        return;
    }

    private function usePHPFilterValidation(array &$phpFileFilterResults)
    {
        foreach ($this->filteredInputArray as $controlName => $files) {
            for ($i = 0, $length = count($files); $i < $length; ++$i) {
                $phpFileFilterResults[$controlName][$i] = filter_var_array($files[$i], $this->phpFieldValidationArray[$controlName], true);
            }
        }
        
        return;
    }
    
    protected function validate()
    {
        //$this->isVoidInput();    //Checks for empty strings.
        /*******************Use PHP validation functions.**********************/

        $testResults = [];
        $fileControlErrors = [];

        $this->usePHPFilterValidation($testResults);
        $this->gatherFileControlErrors($testResults, $fileControlErrors);  //PHP validation filters do not provide error messages automatically.
        $this->setPHPTestResultsAndErrors($fileControlErrors, $testResults);

        //Free up resources.
        $this->phpFieldErrMsgsArray = null;
        $testResults       = null;
        $fileControlErrors = null;
        unset($this->phpFieldErrMsgsArray);
 
        /*******************Use personal validation methods.*******************/

        $this->coreValidatorLogic($testResults, $fileControlErrors);       //My core validation logic does procide error messages automatically.
        $this->setPHPTestResultsAndErrors($fileControlErrors, $testResults);

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
}
