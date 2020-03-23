<?php
declare(strict_types=1);

namespace tfwd\Encoders;

use tfwd\Framework\Base as Base;
use tfwd\Interfaces\CharacterEncodings as CharacterEncodings;
use tfwd\Exceptions\EncodingException as EncodingException;

/**
 * A class concerned with the character encoding of strings.
 * Detects encoding using PHP mb_* functions.
 * 
 * @author Anthony E. Rutledge
 * @version 8-3-2018
 * @copyright (c) 2018, Time Flies Web Design
 */
abstract class Encoder extends Base implements CharacterEncodings
{
    protected $desiredEncoding;
    protected $detectedEncoding;
    protected $supportedEncodings = ['UTF-8', 'ISO-8859-1', 'ASCII'];

    abstract protected function encode();
    
    public function __construct(string $encoding)
    {
        if (!mb_internal_encoding($this->desiredEncoding)) {
            throw new \RuntimeException("Unsupported encoding supplied to the Encoder.");
        }

        if (!mb_detect_order($this->supportedEncodings)) {
            throw new \RuntimeException("Unable to set the encoding detection order in the Encoder.");
        }
        
        if (!mb_substitute_character(0xfffd)) { // Used when unable to convert a character sequence.
            throw new \RuntimeException("Unable to set the substitution character for the Encoder.");
        }
        
        $this->desiredEncoding = $encoding;
    }

    public function getDetectedEncoding()
    {
        return $this->detectedEncoding;
    }
    
    public function encodeIfNotEncoded(string $value): string
    {
        if (!$this->isEncoded($value)) {
            return $this->encode($value);
        }
        
        return $value;
    }
    
    protected function isEncoded(string $value): bool
    {
        $this->detectedEncoding = mb_detect_encoding($value, $this->encodings, true);
        
        if ($this->detectedEncoding === false) {
            throw new EncodingException("Unable to identify character encoding of value.");
        }
        
        return ($this->detectedEncoding === $this->desiredEncoding);
    }

    public function encodeArray(array &$array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
               $value = $this->encodeArray($value);
            } else {
               $value = $this->encodeIfNotEncoded($value);
            }
        }

        unset($value);
    }
}
?>