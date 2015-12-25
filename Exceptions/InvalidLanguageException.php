<?php
/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        23.12.2015
 */
namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Exceptions;

use BiberLtd\Bundle\ExceptionBundle\Services;

class InvalidLanguageException extends Services\ExceptionAdapter {
    /**
     * InvalidLanguageException constructor.
     *
     * @param string                                                                $kernel
     * @param string                                                                $msg
     * @param string                                                                $code
     * @param \BiberLtd\Bundle\MultiLanguageSupportBundle\Exceptions\Exception|null $previous
     */
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