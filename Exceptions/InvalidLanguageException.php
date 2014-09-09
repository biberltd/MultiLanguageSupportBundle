<?php
/**
 * @name        InvalidLanguageException
 * @package		BiberLtd\Bundle\MultiLanguageSupportBundle
 *
 * @author		Can Berkol
 * @version     1.0.1
 * @date        26.06.2014
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Exception to handle cURL connection problems.
 *
 */
namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Exceptions;

use BiberLtd\Bundle\ExceptionBundle\Services;

class InvalidLanguageException extends Services\ExceptionAdapter {
    public function __construct($kernel, $msg = "", $code = 'MLS001', Exception $previous = null) {
        $numeriCode = ord($code[0]).ord($code[1]).ord($code[2]).substr($code, 3, 3);
        parent::__construct(
            $kernel,
            $code.' :: Invalid Language'.PHP_EOL.'BiberLtd\\Core\\Bundles\\MultiLanguageSupport\\Entity\\Language entity is expected.'
                 .PHP_EOL.$msg,
            $numeriCode,
            $previous);
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.1                      Can Berkol
 * 26.06.2014
 * **************************************
 * U __construct()
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 01.08.2013
 * **************************************
 * A __construct()
 *
 */