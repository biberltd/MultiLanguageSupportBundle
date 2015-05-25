<?php
/**
 * @vendor      BiberLtd
 * @package		BiberLtd\Bundle\MultiLanguageSupportBundle
 * @subpackage	Services
 * @name	    MultiLanguageSupportModel
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.1.0
 *
 * @date        25.05.2015
 */

namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Services;
/** Required for better & instant error handling for the support team */
use BiberLtd\Bundle\CoreBundle\CoreModel;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;
/** Entities to be used */
use BiberLtd\Bundle\CoreBundle\Responses\ModelResponse;
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
     * @version         1.0.9
     *
     * @param           object          $kernel
     * @param           string          $dbConnection   Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */
    public function __construct($kernel, $dbConnection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $dbConnection, $orm);
        /**
         * Register entity names for easy reference.
         */
        $this->entity = array(
            'l' 	=> array('name' => 'MultiLanguageSupportBundle:Language', 'alias' => 'l'),
            't' 	=> array('name' => 'MultiLanguageSupportBundle:Translation', 'alias' => 't'),
            'tl' 	=> array('name' => 'MultiLanguageSupportBundle:TranslationLocalization', 'alias' => 'tl'),
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
	 * @name 			deleteLanguage()
	 *
	 * @since			1.0.0
	 * @version         1.0.4
	 * @author          Can Berkol
	 *
	 * @use             $this->deleteLanguages()
	 *
	 * @param           mixed           $language
	 * @return          mixed           $response
	 */
	public function deleteLanguage($language) {
		return $this->deleteLanguages(array($language));
	}

    /**
     * @name 			deleteLanguages()
	 *
     * @since			1.0.0
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Language entities, site ids, url_keys or iso_codes.
     *
     * @return          array           $response
     */
    public function deleteLanguages($collection) {
		$timeStamp = time();
        if (!is_array($collection)) {
            return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
        }
        $countDeleted = 0;
        foreach($collection as $entry){
            if($entry instanceof BundleEntity\Language){
                $this->em->remove($entry);
                $countDeleted++;
            }
            else{
                $response = $this->getLanguage($entry);
				if(!$response->error->exists){
					$entry = $response->result->set;
					$this->em->remove($entry);
					$countDeleted++;
				}
            }
        }
        if($countDeleted < 0){
 			return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, time());
        }
        $this->em->flush();

        return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, time());
    }
	/**
	 * @name 			doesLanguageExist()
	 *
	 * @since			1.0.0
	 * @version         1.0.9
	 * @author          Can Berkol
	 *
	 * @use             $this->getLanguage()
	 *
	 * @param           mixed           $language       Site entity or site id.
	 * @param           bool            $bypass         If set to true does not return response but only the result.
	 *
	 * @return          mixed           $response
	 */
	public function doesLanguageExist($language, $bypass = false) {
		$timeStamp = time();
		$exist = false;

		$response = $this->getLanguage($language);

		if ($response->error->exists) {
			if($bypass){
				return $exist;
			}
			$response->result->set = false;
			return $response;
		}

		$exist = true;

		if ($bypass) {
			return $exist;
		}
		return new ModelResponse(true, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
	/**
	 * @name 			getLanguage()
	 *
	 * @since			1.0.0
	 * @version         1.0.9
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           mixed           $language           string, integer or entity.
	 *
	 * @return          mixed           $response
	 */
	public function getLanguage($language) {
		$timeStamp = time();
		if($language instanceof BundleEntity\Language){
			return new ModelResponse($language, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
		}
		$result = null;
		switch($language){
			case is_numeric($language):
				$result = $this->em->getRepository($this->entity['l']['name'])->findOneBy(array('id' => $language));
				break;
			case is_string($language):
				$result = $this->em->getRepository($this->entity['l']['name'])->findOneBy(array('url_key' => $language));
				if(is_null($result)){
					$result = $this->em->getRepository($this->entity['l']['name'])->findOneBy(array('iso_code' => $language));
				}
				break;
		}
		if(is_null($result)){
			return new ModelResponse($result, 1, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, time());
		}

		return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}

	/**
	 * @name 			insertLanguage()
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
	 * @version         1.0.9
	 * @author          Can Berkol
	 *
	 * @use             $this->createException()
	 *
	 * @param           array           $collection
	 *
	 * @return          array           $response
	 */
	public function insertLanguages($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
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
							$response = $sModel->getSite($value);
							if(!$response->error->exists){
								$entity->$set($response->result->set);
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
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}

    /**
     * @name 			listAllLanguages()
     *
     * @since			1.0.5
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $sortOrder
     * @param           array           $limit
     *
     * @return          array           $response
     */
    public function listAllLanguages($sortOrder = null, $limit = null) {
        return $this->listLanguages(null, $sortOrder, $limit);
    }

    /**
     * @name 			listLanguages()
     *
     * @since			1.0.0
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $filter
     * @param           array           $sortOrder
     * @param           array           $limit
     *
     * @return          array           $response
     */
    public function listLanguages($filter = null, $sortOrder = null, $limit = null) {
		$timeStamp = time();
        if (!is_array($sortOrder) && !is_null($sortOrder)) {
            return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
        }
        $oStr = $wStr = $gStr = $fStr = '';


		$qStr = 'SELECT '.$this->entity['l']['alias']
                    .' FROM '.$this->entity['l']['name'].' '.$this->entity['l']['alias'];

        if (!is_null($sortOrder)) {
            foreach ($sortOrder as $column => $direction) {
                switch ($column) {
                    case 'id':
                    case 'name':
                    case 'url_key':
                    case 'iso_code':
                        $column = $this->entity['l']['alias'] . '.' . $column;
                        break;
                    default:
                        break;
                }
                $oStr .= ' ' . $column . ' ' . strtoupper($direction) . ', ';
            }
			$oStr = rtrim($oStr, ', ');
			$oStr = ' ORDER BY ' . $oStr . ' ';
        }

        if (!is_null($filter)) {
            $fStr = $this->prepareWhere($filter);
            $wStr .= ' WHERE '.$fStr;
        }

        $qStr .= $wStr.$gStr.$oStr;

        $query = $this->em->createQuery($qStr);
		$query = $this->addLimit($query, $limit);

        $result = $query->getResult();

        $totalRows = count($result);
        if ($totalRows < 1) {
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
        }
		return new ModelResponse($result, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
    }
    /**
     * @name            listTranslations()
     *
     * @since           1.0.8
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $₺his->createException()
     *
     * @param           array       $filter         Multi-dimensional array
     * @param           array       $sortOrder
     * @param           array       $limit
     *
     * @return          array       $response
     */
    public function listTranslations($filter = null, $sortOrder = null, $limit = null){
		$timeStamp = time();
        if (!is_array($sortOrder) && !is_null($sortOrder)) {
			return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
        }
        $oStr = $wStr = $gStr = $fStr = '';

		$qStr = 'SELECT '.$this->entity['tl']['alias'].', '.$this->entity['t']['alias']
					.' FROM '.$this->entity['tl']['name'].' '.$this->entity['tl']['alias']
					.' JOIN '.$this->entity['tl']['alias'].'.translation '.$this->entity['t']['alias'];

        if (!is_null($sortOrder)) {
            foreach ($sortOrder as $column => $direction) {
                switch ($column) {
                    case 'domain':
                    case 'key':
                        $column = $this->entity['t']['alias'].'.'.$column;
                        break;
                    case 'phrase':
                        $column = $this->entity['tl']['alias'].'.'.$column;
                        break;
                }
                $oStr .= ' '.$column.' '.strtoupper($direction).', ';
            }
			$oStr = rtrim($oStr, ', ');
			$oStr = ' ORDER BY ' . $oStr . ' ';
        }

        if (!is_null($filter)) {
            $fStr = $this->prepareWhere($filter);
            $wStr .= ' WHERE '.$fStr;
        }

        if($limit != null){
            $lqStr = 'SELECT '.$this->entity['translation']['alias'].' FROM '.$this->entity['translation']['name'].' '.$this->entity['translation']['alias'];
            $lqStr .= $wStr.$gStr.$oStr;
            $lQuery = $this->em->createQuery($lqStr);
            $lQuery = $this->addLimit($lQuery, $limit);
            $result = $lQuery->getResult();
            $selectedIds = array();
            foreach($result as $entry){
                $selectedIds[] = $entry->getId();
            }
            $wStr .= ' AND '.$this->entity['tl']['alias'].'.translation IN('.implode(',', $selectedIds).')';
        }

        $qStr .= $wStr.$gStr.$oStr;
        $query = $this->em->createQuery($qStr);

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

		$totalRows = count($entries);
		if ($totalRows < 1) {
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
		}
		return new ModelResponse($entries, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
    }
    /**
     * @name            listTranslationsOfDomain()
	 *
     * @since           1.0.8
     * @version         1.1.0
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
        $condition = array('column' => $this->entity['t']['alias'].'.domain', 'comparison' => '=', 'value' => $domain);
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
     * @name 			updateLanguage()
     *
     * @since			1.0.0
     * @version         1.0.7
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $data
     *
     * @return          array           $response
     */
    public function updateLanguage($data) {
        return $this->updateLanguages(array($data));
    }
    /**
     * @name 			updateLanguages()
     *  				Updates one or more product details in database.
     *
     * @since			1.0.0
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Product entities or array of entity details.
     *
     * @return          array           $response
     */
    public function updateLanguages($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
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
                    return $this->createException('InvalidParameterException', 'Parameter must be an object with the "id" parameter and id parameter must have an integer value.', 'E:S:003');
                }
                if(!property_exists($data, 'site')){
                    $data->site = 1;
                }
                $response = $this->getLanguage($data->id, 'id');
                if($response->error->exists){
                    return $this->createException('EntityDoesNotExist', 'Language with id '.$data->id, 'E:D:002');
                }
                $oldEntity = $response->result->set;
                foreach($data as $column => $value){
                    $set = 'set'.$this->translateColumnName($column);
                    switch($column){
                        case 'site':
                            $sModel = $this->kernel->getContainer()->get('sitemanagement.model');
                            $response = $sModel->getSite($value);
                            if(!$response->error->exists){
                                $oldEntity->$set($response->result->set);
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
        }
		if($countUpdates > 0){
			$this->em->flush();
			return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, time());
	}
}

/**
 * Change Log
 * **************************************
 * v1.1.0                      25.05.2015
 * Can Berkol
 * **************************************
 * 	BF :: Deprecated call $this->resetResponse() removed.
 *
 * **************************************
 * v1.0.9                      01.05.2015
 * Can Berkol
 * **************************************
 * CR :: The class now uses ModelResponse.
 * CR :: $this->entity keys now use shortened values.
 *
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