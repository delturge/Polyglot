<?php
declare(strict_types=1);

namespace tfwd\Autoloaders;

use tfwd\Autoloaders\Autoloader as Autoloader;

require 'Autoloader.php';

/**
 * PHP-FIG PSR-4 compliant class autoloader
 * for the "tfwd" namespace prefix.
 *
 * @author delturge
 */
final class TimeFliesWebDesignAutoloader extends Autoloader
{
    protected const NAMESPACE_PREFIX = 'tfwd\\';
    
    private function __construct()
    {
        ;
    }
   
    private function __clone()
    {
        ;
    }
    
    /**
     * Autoloader method for classes in the "library" project directory.
     */
    public static function autoloadFromLibraryPath($qualifiedClassName)
    {
        $namespacePrefixLength = self::getNamespacePrefixLength(self::NAMESPACE_PREFIX);

        if (self::isCorrectAutoloader(self::NAMESPACE_PREFIX, $qualifiedClassName, $namespacePrefixLength)) {
            return;
        }

        require self::getFileFromDirectory('/library/', $qualifiedClassName, $namespacePrefixLength);
    }
    
    /**
     * Autoloader method for classes in the "Application" project directory.
     */
    public static function autoloadFromApplicationPath($qualifiedClassName)
    {
        $namespacePrefixLength = self::getNamespacePrefixLength(self::NAMESPACE_PREFIX);

        if (self::isCorrectAutoloader(self::NAMESPACE_PREFIX, $qualifiedClassName, $namespacePrefixLength)) {
            return;
        }

        require self::getFileFromDirectory('/Application/', $qualifiedClassName, $namespacePrefixLength);
    }
    
    /**
     * Registers autoloader methods.
     * 
     * @throws \RuntimeException
     */
    public static function init()
    {
        if (!spl_autoload_register(['self', 'autoloadFromLibraryPath'])) {
            throw new \RuntimeException('Autoloader failed to initialize spl_autoload_register() for the /library/ path.');
        }
        
        if (!spl_autoload_register(['self', 'autoloadFromApplicationPath'])) {
            throw new \RuntimeException('Autoloader failed to initialize spl_autoload_register() for the /application/ path.');
        }
    }
}
?>