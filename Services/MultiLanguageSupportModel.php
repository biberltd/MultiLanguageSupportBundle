<?php
/**
 * MultiLanguageSupportModel Class
 *
 * This class acts as a database proxy model for MultiLanguageSupportBundle functionalities.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\MultiLanguageSupportBundle
 * @subpackage	Services
 * @name	    MultiLanguageSupportModel
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.8
 *
 * @date        09.04.2014
 *
 * =============================================================================================================
 * !! INSTRUCTIONS ON IMPORTANT ASPECTS OF MODEL METHODS !!!
 *
 * Each model function must return a $response ARRAY.
 * The array must contain the following keys and corresponding values.
 *
 * $response = array(
 *              'result'    =>   An array that contains the following keys:
 *                               'set'         Actual result set returned from ORM or null
 *                               'total_rows'  0 or number of total rows
 *                               'last_insert_id' The id of the item that is added last (if insert action)
 *              'error'     =>   true if there is an error; false if there is none.
 *              'code'      =>   null or a semantic and short English string that defines the error concanated
 *                               with dots, prefixed with err and the initials of the name of model class.
 *                               EXAMPLE: err.amm.action.not.found success messages have a prefix called scc..
 *
 *                               NOTE: DO NOT FORGET TO ADD AN ENTRY FOR ERROR CODE IN BUNDLE'S
 *                               RESOURCES/TRANSLATIONS FOLDER FOR EACH LANGUAGE.
 * =============================================================================================================
 * TODOs:
 * Do not forget to implement ORDER, AND PAGINATION RELATED FUNCTIONALITY
 *
 * @todo v1.0.1     list_all_languages($sortorder, $limit)    uses list_languages()
 * @todo v1.0.1     list_all_ltr_languages($sortorder, $limit)  uses list_languages()
 * @todo v1.0.1     list_all_ltr_languages_of_sites($sites, $sortorder, $limit)     uses list_languages()
 * @todo v1.0.1     list_all_ltr_languages_of_site($site, $sortorder, $limit)       uses list_all_ltr_languages_of_sites()
 * @todo v1.0.1     list_languages($filter, $sortorder, $limit)
 * @todo v1.0.1     list_languages_of_site($site, $sortorder, $limit)       uses list_languages_of_site()
 * @todo v1.0.1     list_languages_of_sites($sites, $sortorder, $limit)     uses list_languages()
 *
 */

namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Services;
/** Required for better & instant error handling for the support team */
use BiberLtd\Bundle\CoreBundle\CoreModel;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;
/** Entities to be used */
use BiberLtd\Bundle\MultiLanguageSupportBundle\Entity as BundleEntity;
/** External Services To Use */
use BiberLtd\Bundle\SiteManagementBundle\Services as SMServices;

class MultiLanguageSupportModel extends CoreModel {

    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.2
     *
     * @param           object          $kernel
     * @param           string          $db_connection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */
    public function __construct($kernel, $db_connection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $db_connection, $orm);
        /**
         * Register entity names for easy reference.
         */
        $this->entity = array(
            'language' => array('name' => 'MultiLanguageSupportBundle:Language', 'alias' => 'l'),
            'translation' => array('name' => 'MultiLanguageSupportBundle:Translation', 'alias' => 't'),
            'translation_localization' => array('name' => 'MultiLanguageSupportBundle:TranslationLocalization', 'alias' => 'tl'),
        );
    }

    /**
     * @name            __destruct()
     *                  Destructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }

    /**
     * @name 			deleteLanguages()
     *  				Deletes provided languages from database. If the language does not exist, throws
     *                  LanguageDoesNotExistException.
     *
     * @since			1.0.0
     * @version         1.0.7
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Language entities, site ids, url_keys or iso_codes.
     *
     * @return          array           $response
     */
    public function deleteLanguages($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterValueException', 'Array', 'err.invalid.parameter.collection');
        }
        $countDeleted = 0;
        foreach($collection as $entry){
            if($entry instanceof BundleEntity\Language){
                $this->em->remove($entry);
                $countDeleted++;
            }
            else{
                switch($entry){
                    case is_numeric($entry):
                        $response = $this->getLanguage($entry, 'id');
                        break;
                    case is_string($entry):
                        $response = $this->getLanguage($entry, 'url_key');
                        if($response['error']){
                            $response = $this->getLanguage($entry, 'iso_code');
                        }
                        break;
                }
                if($response['error']){
                    $this->createException('EntryDoesNotExist', $entry, 'err.invalid.entry');
                }
                $entry = $response['result']['set'];
                $this->em->remove($entry);
                $countDeleted++;
            }
        }
        if($countDeleted < 0){
            $this->response['error'] = true;
            $this->response['code'] = 'err.db.fail.delete';

            return $this->response;
        }
        $this->em->flush();
        $this->response = array(
            'rowCount' => 0,
            'result' => array(
                'set' => null,
                'total_rows' => $countDeleted,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.deleted',
        );
        return $this->response;
    }

    /**
     * @name 			deleteLanguage()
     *  				Deletes an existing language from database. If the language does not exist, throws
     *                  LanguageDoesNotExistException.
     *
     * @since			1.0.0
     * @version         1.0.4
     * @author          Can Berkol
     *
     * @use             $this->deleteLanguages()
     *
     * @param           mixed           $language           Language entity or language id.
     * @return          mixed           $response
     */
    public function deleteLanguage($language) {
        return $this->deleteLanguages(array($language));
    }

    /**
     * @name 			listAllLanguages()
     *  				List all registered languages.
     *
     * @since			1.0.5
     * @version         1.0.5
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $sortorder              Array
     *                                      'column'            => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     *
     * @return          array           $response
     */
    public function listAllLanguages($sortorder = null, $limit = null) {
        return $this->listLanguages(null, $sortorder, $limit);
    }

    /**
     * @name 			listLanguages()
     *  				List registered languages from database based on a variety of conditions.
     *
     * @since			1.0.0
     * @version         1.0.4
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $filter             Multi-dimensional array
     *
     *                                  Example:
     *                                  $filter = array(
     *                                      'address_type'  => array('in' => array(2,5),
     *                                                               'not_in' => array(4)
     *                                                              ),
     *                                      'member'        => array('in' => array(Member1, Member2)),
     *                                      'tax_id'        => 21312412,
     *                                  );
     *
     *                                  Each array element defines an AND condition.
     *                                  Each array element contains another array with keys
     *                                  in and not_in to include and to exclude data.
     *                                  Each nested array element that is containted in condition states
     *                                  an OR condition.
     *
     * @param           array           $sortorder              Array
     *                                      'column'            => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     * @param           string          $query_str              custom query string
     *
     * @return          array           $response
     */
    private function listLanguages($filter = null, $sortorder = null, $limit = null, $query_str = null) {
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrderException', '', 'err.invalid.parameter.sortorder');
        }
        /**
         * Add filter checks to below to set join_needed to true.
         */
        /**         * ************************************************** */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        if (is_null($query_str)) {
            $query_str = 'SELECT ' . $this->entity['language']['alias']
                    . ' FROM ' . $this->entity['language']['name'] . ' ' . $this->entity['language']['alias'];
        }
        /**
         * Prepare ORDER BY section of query.
         */
        if ($sortorder != null) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'name':
                    case 'url_key':
                    case 'iso_code':
                        $column = $this->entity['language']['alias'] . '.' . $column;
                        break;
                    default:
                        return $this->createException('InvalidSortOrderException', 'id, namee, url_keey, iso_code', 'err.invalid.parameter.sortorder');
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepareWhere($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }

        $query_str .= $where_str . $group_str . $order_str;

        $query = $this->em->createQuery($query_str);

        /**
         * Prepare LIMIT section of query
         */
        if ($limit != null) {
            if (isset($limit['start']) && isset($limit['count'])) {
                /** If limit is set */
                $query->setFirstResult($limit['start']);
                $query->setMaxResults($limit['count']);
            } else {
                new CoreExceptions\InvalidLimitException($this->kernel, '');
            }
        }
        /**
         * Prepare & Return Response
         */
        $result = $query->getResult();

        $total_rows = count($result);
        if ($total_rows < 1) {
            $this->response['error'] = true;
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $result,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name            listTranslations()
     *                  List translations from database based on a variety of conditions.
     *
     * @since           1.0.8
     * @version         1.0.8
     * @author          Can Berkol
     *
     * @use             $₺his->createException()
     *
     * @param           array       $filter         Multi-dimensional array
     * @param           array       $sortorder
     * @param           array       $limit
     *
     * @param           string      $query_str      If a custom query string needs to be defined.
     *
     * @return          array       $response
     */
    public function listTranslations($filter = null, $sortorder = null, $limit = null, $query_str = null){
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrder', '', 'err.invalid.parameter.sortorder');
        }
        /**
         * Add filter checks to below to set join_needed to true.
         */
        /**         * ************************************************** */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';
        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        if (is_null($query_str)) {
            $query_str = 'SELECT ' . $this->entity['translation_localization']['alias'] . ', ' . $this->entity['translation']['alias']
                . ' FROM ' . $this->entity['translation_localization']['name'] . ' ' . $this->entity['translation_localization']['alias']
                . ' JOIN ' . $this->entity['translation_localization']['alias'] . '.translation ' . $this->entity['translation']['alias'];
        }
        /**
         * Prepare ORDER BY section of query.
         */
        if ($sortorder != null) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'domain':
                    case 'key':
                        $column = $this->entity['translation']['alias'] . '.' . $column;
                        break;
                    case 'phrase':
                        $column = $this->entity['translation_localization']['alias'] . '.' . $column;
                        break;
                }
                $order_str .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepareWhere($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }

        if($limit != null){
            $lqStr = 'SELECT '.$this->entity['translation']['alias'].' FROM '.$this->entity['translation']['name'].' '.$this->entity['translation']['alias'];
            $lqStr .= $where_str.$group_str.$order_str;
            $lQuery = $this->em->createQuery($lqStr);
            $lQuery = $this->addLimit($lQuery, $limit);
            $result = $lQuery->getResult();
            $selectedIds = array();
            foreach($result as $entry){
                $selectedIds[] = $entry->getId();
            }
            $where_str .= ' AND '.$this->entity['translation_localization']['alias'].'.translation IN('.implode(',', $selectedIds).')';
        }

        $query_str .= $where_str . $group_str . $order_str;
        $query = $this->em->createQuery($query_str);

        /**
         * Prepare & Return Response
         */
        $result = $query->getResult();

        $entries = array();
        $unique = array();
        foreach ($result as $entry) {
            $id = $entry->getTranslation()->getId();
            if (!isset($unique[$id])) {
                $entries[] = $entry->getTranslation();
                $unique[$id] = $entry->getTranslation();
            }
        }
        unset($unique);
        $total_rows = count($entries);
        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $entries,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name            listTranslationsOfDomain()
     *                  List all translations of a specific domain.
     *
     * @since           1.0.8
     * @version         1.0.8
     * @author          Can Berkol
     *
     * @use             $this->listTranslations()
     *
     * @param           string          $domain
     * @param           array           $sortorder
     * @param           array           $limit
     *
     * @return          array           $response
     */
    public function listTranslationsOfDomain($domain, $sortorder = null, $limit = null){
        $this->resetResponse();
        $condition = array('column' => $this->entity['translation']['alias'].'.domain', 'comparison' => '=', 'value' => $domain);
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => $condition,
                )
            )
        );
        return $this->listTranslations($filter, $sortorder, $limit);
    }
    /**
     * @name 			getLanguage()
     *  				Returns details of a language.
     *
     * @since			1.0.0
     * @version         1.0.6
     * @author          Can Berkol
     *
     * @use             $this->listLanguages()
     * @use             $this->createExceptionn()
     *
     * @param           mixed           $language           Language entity or site id.
     * @param           string          $by                 id, iso_code or url_key
     *
     * @return          mixed           $response
     */
    public function getLanguage($language, $by = 'id') {
        if ($by !== 'id' && $by !== 'url_key' && $by !== 'iso_code') {
            return $this->createException('InvalidParameterException', 'id, iso_code, url_key', 'err.invalid.parameter.by');
        }

        if (!is_object($language) && !is_int($language) && !is_string($language)) {
            return $this->createException('InvalidParameterException', 'Language entity or string representing url_key, iso_code, or id', 'err.invalid.parameter.language');
        }


        if (is_object($language)) {
            if (!$language instanceof BundleEntity\Language) {
                return $this->createException('InvalidParameterException', 'Language', 'err.invalid.parameter.language');
            }
            $language = $language->getId();
        }

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['language']['alias'] . '.' . $by, 'comparison' => '=', 'value' => $language),
                )
            )
        );

        $response = $this->listLanguages($filter, null, array('start' => 0, 'count' => 1));

        if ($response['error']) {
            return $response;
        }

        $collection = $response['result']['set'];

        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $collection[0],
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name 			doesLanguageExist()
     *  				Checks if language exists in database.
     *
     * @since			1.0.0
     * @version         1.0.4
     * @author          Can Berkol
     *
     * @use             $this->getLaanguage()
     *
     * @param           mixed           $language       Site entity or site id.
     * @param           string          $by             all, id, iso_code or url_key
     * @param           bool            $bypass         If set to true does not return response but only the result.
     *
     * @return          mixed           $response
     */
    public function doesLanguageExist($language, $by = 'all', $bypass = false) {
        $exist = false;
        if ($by == 'all') {
            $response_by_id = $this->getLanguage($language, 'id');
            $response_by_iso = $this->getLanguage($language, 'iso_code');
            $response_by_key = $this->getLanguage($language, 'url_key');

            if (!$response_by_id['result']['total_rows'] > 0 || !$response_by_iso['result']['total_rows'] > 0 || !$response_by_key['result']['total_rows'] > 0) {
                $exist = true;
            }
        } else {
            $response = $this->getLanguage($language, $by);
        }

        if ($response['error']) {
            return $response;
        }

        if ($response['result']['total_rows'] > 0) {
            $exist = true;
        }

        if ($bypass) {
            return $exist;
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $exist,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			insertLanguagge()
     *  				Inserts one language into database.
     *
     * @since			1.0.0
     * @version         1.0.7
     * @author          Can Berkol
     *
     * @use             $this->insertLanguages()
     *
     * @param           mixed           $data      Language Entity or a collection of post input that stores site details.
     *
     * @return          array           $response
     */
    public function insertLanguage($data) {
        return $this->insertLanguages(array($data));
    }
    /**
     * @name 			insertLanguages()
     *  				Inserts one or more languages into database.
     *
     * @since			1.0.0
     * @version         1.0.7
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Site entities or array of site detais array.
     *
     * @return          array           $response
     */
    public function insertLanguages($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $countInserts = 0;
        $insertedItems = array();
        foreach($collection as $data){
            if($data instanceof BundleEntity\Language){
                $entity = $data;
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            }
            else if(is_object($data)){
                $entity = new BundleEntity\Language;
                if(!property_exists($data, 'site')){
                    $data->site = 1;
                }
                foreach($data as $column => $value){
                    $set = 'set'.$this->translateColumnName($column);
                    switch($column){
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value, 'id');
                            if(!$response['error']){
                                $entity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        default:
                            $entity->$set($value);
                            break;
                    }
                }
                $this->em->persist($entity);
                $insertedItems[] = $entity;
                $countInserts++;
            }
            else{
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        if($countInserts > 0){
            $this->em->flush();
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $insertedItems,
                'total_rows' => $countInserts,
                'last_insert_id' => $entity->getId(),
            ),
            'error' => false,
            'code' => 'scc.db.insert.done',
        );
        return $this->response;
    }
    /**
     * @name 			updateLanguage()
     *  				Update one language in database.
     *
     * @since			1.0.0
     * @version         1.0.7
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $data      Language Entity or a collection of post input that stores site details.
     *
     * @return          array           $response
     */
    public function updateLanguage($data) {
        /** Parameter must be an array */
        return $this->updateLanguages(array($data));
    }
    /**
     * @name 			updateLanguages()
     *  				Updates one or more product details in database.
     *
     * @since			1.0.0
     * @version         1.0.7
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Product entities or array of entity details.
     *
     * @return          array           $response
     */
    public function updateLanguages($collection) {
        $this->resetResponse();
        /** Parameter must be an array */
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $countUpdates = 0;
        $updatedItems = array();
        foreach($collection as $data){
            if($data instanceof BundleEntity\Language){
                $entity = $data;
                $this->em->persist($entity);
                $updatedItems[] = $entity;
                $countUpdates++;
            }
            else if(is_object($data)){
                if(!property_exists($data, 'id') || !is_numeric($data->id)){
                    return $this->createException('InvalidParameterException', 'Each data must contain a valid identifier id, integer', 'err.invalid.parameter.collection');
                }
                if(!property_exists($data, 'site')){
                    $data->site = 1;
                }
                $response = $this->getLanguage($data->id, 'id');
                if($response['error']){
                    return $this->createException('EntityDoesNotExist', 'ProductAttribute with id '.$data->id, 'err.invalid.entity');
                }
                $oldEntity = $response['result']['set'];
                foreach($data as $column => $value){
                    $set = 'set'.$this->translateColumnName($column);
                    switch($column){
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value, 'id');
                            if(!$response['error']){
                                $oldEntity->$set($response['result']['set']);
                            }
                            else{
                                new CoreExceptions\SiteDoesNotExistException($this->kernel, $value);
                            }
                            unset($response, $sModel);
                            break;
                        case 'id':
                            break;
                        default:
                            $oldEntity->$set($value);
                            break;
                    }
                    if($oldEntity->isModified()){
                        $this->em->persist($oldEntity);
                        $countUpdates++;
                        $updatedItems[] = $oldEntity;
                    }
                }
            }
            else{
                new CoreExceptions\InvalidDataException($this->kernel);
            }
        }
        if($countUpdates > 0){
            $this->em->flush();
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $updatedItems,
                'total_rows' => $countUpdates,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.update.done',
        );
        return $this->response;
    }
}

/**
 * Change Log
 * **************************************
 * v1.0.8                      Can Berkol
 * 09.04.2014
 * **************************************
 * A listTranslations()
 * A listTranslationsOfDomain()
 *
 * **************************************
 * v1.0.7                      Can Berkol
 * 29.01.2014
 * **************************************
 * U deleteLangauges()
 * U insertLanguage()
 * U insertLanguages()
 * U updateLanguage()
 * U updateLanguages()
 *
 * **************************************
 * v1.0.6                      Can Berkol
 * 30.11.2013
 * **************************************
 * B getLanguage()
 *
 * **************************************
 * v1.0.5                      Can Berkol
 * 22.11.2013
 * **************************************
 * A listAllLanguages()
 * U listLanguages() is now private.
 *
 * **************************************
 * v1.0.4                      Can Berkol
 * 16.11.2013
 * **************************************
 * M Class now extends CoreModel
 * M Methods are now camelCase.
 *
 * **************************************
 * v1.0.3                      Can Berkol
 * 06.11.2013
 * **************************************
 * M Response codes updated.
 *
 * **************************************
 * v1.0.2                      Can Berkol
 * 10.10.2013
 * **************************************
 * M Overall bug fixes and message code sanitization.
 *
 * **************************************
 * v1.0.1                      Can Berkol
 * 16.08.2013
 * **************************************
 * B list_languages() Null filter query problem fixed.
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 05.08.2013
 * **************************************
 * A __construct()
 * A __destruct()
 * A delete_language()
 * A delete_languages()
 * A does_language_exist()
 * A getLanguage()
 * A insert_language()
 * A insert_languages()
 * A list_languages()
 * A update_language()
 * A update_languages()
 */