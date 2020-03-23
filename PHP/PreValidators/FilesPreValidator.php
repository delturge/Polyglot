<?php
namespace Isaac\PreValidators;

use Isaac\Encoders\Encoder as Encoder;

/**
 * @author wcmport
 */
abstract class FilesPreValidator extends PreValidator 
{
    /* Properties */    
    private $numFiles = null;
    private $minFiles = null;
    private $maxFiles = null;
    private $maxFilesPerControl = null;

    /* Constructor */
    protected function __construct(array $inputArray, $inputSource, $minElements, $maxElements, array $inputMetaArray, array $transientInputs, Encoder $encoder, $minFiles, $maxFiles, array $maxFilesPerControl) 
    {
        parent::__construct($inputArray, $inputSource, $minElements, $maxElements, $inputMetaArray, $transientInputs, $encoder);
        $this->setMinFiles($minFiles, $maxFiles);
        $this->setMaxFiles($maxFiles);
        $this->restructureFilesArray();
        $this->setNumFiles($this->countFiles());
        $this->maxFilesPerControl = $maxFilesPerControl;
    }
    
    private function setMinFiles($min, $max)
    {
        if (!$this->isNonNegInt($min)) {
            throw new \DomainException("The minimum number of files to pre-validate must be zero (0) or greater. {$min} provided.");
        }
        
        if (!$this->isPosInt($max)) {
            throw new \DomainException("The maximum number of files to pre-validate must be greater than zero (0). {$max} provided.");
        }
        
        if ($min > $max) {
            throw new \DomainException("The minimum number of files to pre-validate must be less than the maximum ({$max}). {$min} provided.");
        }

        $this->minFiles = $num;
        return;
    }
    
    private function setMaxFiles($num)
    {
        if (!$this->isPosInt($num)) {
            throw new \DomainException("The maximum number of files to pre-validate must be greater than zero (0). {$num} provided.");
        }
        
        $this->maxFiles = $num;
        return;
    }

    private function setNumFiles($num)
    {           
        if (!$this->isNonNegInt($num)) {
            throw new \DomainException("The number of submitted files must be a zero(0) or more. {$num} given");
        }

        $this->numFiles = $num;
        return;
    }
    
    private function isGoodRestructure($num)
    {
        if ($num <= $this->maxFiles) {
            return;
        } else {
            throw new RangeException("Too many files ({$num}) submitted.");
        }
    }
    
    private function restructureMultipleFileSubmission($control, array $array, array &$files, &$restructures)
    {
        for ($i = 0, $length = count($array['error']); $i < $length; ++$i) {
            $files[$control][$i] = [
                'name'     => $array['name'][$i],
                'type'     => $array['type'][$i],
                'size'     => $array['size'][$i],
                'tmp_name' => $array['tmp_name'][$i],
                'error'    => $array['error'][$i]
            ];

            ++$restructures;
            $this->isGoodRestructure($restructures);
        }
        
        return;
    }

    /**
     * Changes the traditional $_FILES format so that each file is represented
     * as an array, within a numerically indexed array, inside of an array for
     * the input control (indexed by name="name").
     * http://php.net/manual/en/features.file-upload.post-method.php
     * coreywelch+phpnet at gmail dot com
     */
    private function restructureFilesArray()
    {
        $files = [];
        $restructures = 0;

        foreach ($this->inputArray as $control => $array) {
            if (!is_array($array['error'])) {
                $files[$control][0] = $array;
                ++$restructures;
                $this->isGoodRestructure($restructures);
                continue;
            }
            
            $this->restructureMultipleFileSubmission($control, $array, $files, $restructures);
        }

        return $files;
    }

    protected function countInputs()
    {
        return count($this->inputArray);
    }

    /**
     * Determines if an entry in $_FILES is actually a file.
     */
    private function isPostFile(array $file)
    {
        return  ($this->isNonNegInt($file['error'])) &&
                    $this->isNonNegInt($file['size']) &&
                    !empty($file['tmp_name']) &&
                    is_uploaded_file($file['tmp_name']) &&
                    !empty($file['name']) &&
                    !empty($file['type']);
    }

    private function countFiles()
    {
        $num = 0;

        foreach ($this->inputArray as $files) {
            $length = count($files);

            for ($i = 0; $i < $length; ++$i) {
                if ($this->isPostFile($files[$i])) {
                    ++$num;
                }
            }
        }

        return $num;
    }
    
    
    protected function pruneInputMetaArray()
    {
        parent::pruneInputMetaArray();

        for ($i = 0, $length = count($this->transientInputs); $i < $length; ++$i) {
            $tInput = $this->transientInputs[$i];

            if (!isset($this->inputArray[$tInput])) {  //If the transient HTML control is not successful / present.
                unset($this->maxFilesPerControl[$tInput]); //Remove pre-validation instructions for it.
            }
        }
        
        return;
    }
    
    private function hasMinNumFiles()
    {
        if ($this->numFiles < $this->minFiles) {
            throw new \RangeException("{$this->inputSource} cannot have less than {$this->mimFiles} files. Too few ({$this->numFiles}) submitted.");
        }

        return true;
    }
    
    private function hasNotExceededMaxNumFiles()
    {
        if ($this->numFiles > $this->maxFiles) {
            throw new \RangeException("{$this->inputSource} cannot have more than {$this->maxFiles} files. Too many ({$this->numFiles}) submitted.");
        }

        return true;
    }

    private function hasGoodNumFilesPerControl()
    {
        foreach ($this->inputArray as $control => $files) {
            $numSubmittedFiles = count($files);
            $maxFiles          = $this->maxFilesPerControl[$control];

            if ($numSubmittedFiles > $maxFiles) {
                throw new SecurityException("The file input control for --{$control}-- uploaded too many files! {$maxFiles} allowed. {$numSubmittedFiles} submitted.");
            }
        }
        
        return;
    }
    
    private function checkForElements(array $required)
    {
        foreach (array_keys($required) as $control) {
            if (!isset($this->inputArray[$control])) {
                throw new OutOfBoundsException("The index {$control} does not exist in {$this->inputSource}!");
            }
        }

        return true;
    }

    private function checkEncoding()
    {
        foreach ($this->inputArray as $control => &$array) {
            for ($i = 0, $length = count($array); $i < $length; ++$i) {
                $array[$i]['name'] = $this->encoder->encodeIfNotEncoded($array[$i]['name']);
                $array[$i]['type'] = $this->encoder->encodeIfNotEncoded($array[$i]['type']);
            }
        }

        return true;
    }
    
    private function hasGoodFileSizes()
    {
        foreach ($this->inputArray as $control => $files) {
            for ($i = 0, $length = count($files); $i < $length; ++$i) {
               if ($files[$i]['size'] > $this->inputMetaArray[$control]['size']) {
                   throw new SecurityException('An uploaded file exceeds upload size limits!');
               }
            }
        }

        return true;
    }

    private function hasGoodFileNameLengths()
    {
        foreach ($this->inputArray as $control => $files) {
            for ($i = 0, $length = count($files); $i < $length; ++$i) {
               if (mb_strlen($files[$i]['name'], 'UTF-8') > $this->inputMetaArray[$control]['name']) {
                   throw new SecurityException('An uploaded file name exceeds the length limit!');
               }
            }
        }

        return true;
    }

    private function hasGoodMimeTypeLengths()
    {
        foreach ($this->inputArray as $control => $files) {
            for ($i = 0, $length = count($files); $i < $length; ++$i) {
               if(mb_strlen($files[$i]['type'], 'UTF-8') > $this->inputMetaArray[$control]['type']) {
                   throw new SecurityException('An uploaded file name exceeds the length limit!');
               }
            }
        }

        return true;
    }
    
    protected function hasGoodSizedElements()  //Checks all elements of a file's array.
    {
        return $this->hasGoodFileSizes() && $this->hasGoodFileNameLengths() && $this->hasGoodMimeTypeLengths();  //Only check user input.
    }
    
    public function validate()  //Calls the "Four Horsemen" of pre-validation.
    {
        if ($this->hasMinNumElements() &&
                $this->hasNotExceededMaxNumElements() &&
                $this->hasRequiredElements() &&
                $this->hasMinNumFiles() &&
                $this->hasNotExceededMaxNumFiles() &&
                $this->hasGoodNumFilesPerControl() &&
                $this->hasGoodEncoding() &&
                $this->hasGoodSizedElements()) {
            return true;
        }

        throw new SecurityException('The HTTP request is malformed at the PreValidator level. HTML input="file" or query string may have been tampered with.');
    }
}
