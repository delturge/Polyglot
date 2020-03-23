<?php
declare(strict_types=1);

namespace tfwd\Encoders;

use tfwd\Encoder\Encoder as Encoder;
use tfwd\Exceptions\EncodingException as EncodingException;


/**
 * A child class that handles the details of detecting
 * UTF-8 encoding and attempts to convert to UTF-8 if 
 * necessary.
 * 
 * @author Anthony E. Rutledge
 * @version 8-3-2018
 * @copyright (c) 2018, Time Flies Web Design
 */
class UTF8Encoder extends Encoder
{
    public function __construct()
    {
        parent::__construct(self::UTF_8);
    }
    
    protected function encode(string $value): string
    {
        $convertedValue = mb_convert_encoding($value, $this->desiredEncoding, $this->detectedEncoding);
        
        if (!$this->isEncoded($convertedValue)) {
            throw new EncodingException("A submitted value was not able to be represented in {$this->desiredEncoding} format.");
        }
        
        if (!$this->isIso88591Equivalent($convertedValue)) {
            throw new EncodingException("A submitted value does not translate to the ISO-8859-1 character set.");
        }
        
        $this->detectedEncoding = null;
        return $convertedValue;
    }
    
    private function isIso88591Equivalent($value): bool
    {
        return ($value === utf8_encode(utf8_decode($value)));
    }
}
?>