<?php
declare(strict_types=1);

namespace tfwd\Autoloaders;

use tfwd\Framework\Base as Base;

require 'Base.php';

/**
 * A PHP-FIG PSR-4 compliant autoloader class.
 * 
 * Note: If you need autoloading for more
 * than one NAMESPACE_PREFIX, you would be
 * wise to re-factor Autoloader to be abstact.
 * 
 * abstratc class Autoloader
 * final class Tfwd extends Autoloader
 * final class OtherNamespacePrefix extends Autoloader 
 */
abstract class Autoloader extends Base
{
    private const PROJECT_ROOT = 'c:/public/www/';
    private const PROJECT = 'dccc.edu';
    private const FILE_SUFFIX = '.php';
    private const SAME_STRING = 0;
    
    private function __construct()
    {
        ;
    }
   
    private function __clone()
    {
        ;
    }

    private static function testFile(string $file)
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("The file {$file} does not exist!");
        }

        if (!is_file($file)) {
            throw new \RuntimeException("The file {$file} is not a regular file!");
        }

        if (!is_readable($file)) {
            throw new \RuntimeException("The file {$file} is not readable!");
        }
    }
    
    private static function getIncludeFileName(string $baseDir, string $qualifiedClassName, int $namespacePrefixLength): string
    {
        return $baseDir . str_replace('\\', '/', substr($qualifiedClassName, $namespacePrefixLength)) . self::FILE_SUFFIX; //substr() returns the string after $nsPrefix.
    }
    
    private static function getBaseDirName(string $subDirectory): string
    {
        return self::PROJECT_ROOT . self::PROJECT . $subDirectory;
    }
    
    protected static function getNamespacePrefixLength(string $namespacePrefix): int
    {
        return strlen($namespacePrefix);
    }
    
    protected static function isCorrectAutoloader(string $namespacePrefix, string $qualifiedClassName, int $namespacePrefixLength): bool
    {
        return strncmp($namespacePrefix, $qualifiedClassName, $namespacePrefixLength) !== self::SAME_STRING;
    }
    
    protected static function getFileFromDirectory(string $subDirectory, string $qualifiedClassName, int $namespacePrefixLength): string
    {
        $baseDir = self::getBaseDirName($subDirectory);
        $file = self::getIncludeFileName($baseDir, $qualifiedClassName, $namespacePrefixLength);
        self::testFile($file);
        return $file;
    }
}
?>