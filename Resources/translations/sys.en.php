<?php
/**
 * sys.en.php
 *
 * This file registers the bundle's system (error and success) messages in English.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\MultiLanguageSupportBundle
 * @subpackage	Resources
 * @name	    sys.en.php
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 * @date        05.08.2013
 *
 * =============================================================================================================
 * !!! IMPORTANT !!!
 *
 * Depending your environment run the following code after you have modified this file to clear Symfony Cache.
 * Otherwise your changes will NOT take affect!
 *
 * $ sudo -u apache php app/console cache:clear
 * OR
 * $ php app/console cache:clear
 * =============================================================================================================
 * TODOs:
 * None
 */
/** Nested keys are accepted */
return array(
    /** Error messages */
    'err'       => array(
        /** Multi Language Support Model */
        'mlsm'   => array(
            'duplicate'     => array(
                'language'     =>'A language with the same id, iso_code or url_key already exists in database.',
            ),
            'invalid'       =>  array(
                    'entity'        => array(
                                'language'  => '"Language" entity is expected; however, some other value is found.',
                    ),
                    'parameter'     =>  array(
                                'by'        => 'The "$by" parameter accepts only one of the "entity," "id," "iso_code," or "url_key" strings.',
                                'language'  => 'The "$language" parameter accepts only one array value with key => value pairs or a Language Entity.',
                                'languages' => 'The "$languages" parameter accepts only Array values.',
                                'sortorder' => 'The "$sortorder" parameter accepts only one-dimensional array with key => value pairs. Accepted keys are: id, name, url_key, iso_code',
                    ),
            ),
            'not_found'     => 'The language you have requested is not found in database.',
            'unknown'                   => 'An unknown error occured or the MultiLanguageSupportModel has NOT been created.',
        ),
    ),
    /** Success messages */
    'scc'       => array(
        /** Multi Language Support Model */
        'mlsm'   => array(
            'default'       => 'Database transaction is processed successfuly.',
            'deleted'       => 'Selected entries have been succesfully deleted.',
            'inserted'      => array(
                        'multiple'      => 'The data has been successfully added to the database.',
                        'single'        => 'The data has been successfully added to the database.',
            ),
            'updated'       => array(
                'multiple'      => 'The data has been successfully updated.',
                'single'        => 'The data has been successfully updated.',
            ),
        ),
    ),
);
/**
 * Change Log
 * **************************************
 * v1.0.0                      Can Berkol
 * 03.08.2013
 * **************************************
 * A err
 * A err.mlsm
 * A err.mlsm.duplicate
 * A err.mlsm.duplicate.language
 * A err.mlsm.invalid
 * A err.mlsm.invalid.entity
 * A err.mlsm.invalid.entity.language
 * A err.mlsm.invalid.parameter
 * A err.mlsm.invalid.parameter.by
 * A err.mlsm.invalid.parameter.languages
 * A err.mlsm.unknown
 * A scc
 * A scc.smm
 * A scc.smm.default
 * A scc.smm.deleted
 * A scc.smm.inserted
 * A scc.smm.inserted.multiple
 * A scc.smm.inserted.single
 * A scc.smm.updated
 * A scc.smm.updated.multiple
 * A scc.smm.updated.single
 */