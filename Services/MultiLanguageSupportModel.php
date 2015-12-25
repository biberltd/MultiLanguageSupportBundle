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
	 * Destructor
	 */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }

	/**
	 * @param mixed $language
	 *
	 * @return array
	 */
	public function deleteLanguage($language) {
		return $this->deleteLanguages(array($language));
	}

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function deleteLanguages(array $collection) {
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
	 * @param mixed $language
	 * @param bool $bypass
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool|mixed
	 */
	public function doesLanguageExist($language, \bool $bypass = false) {
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
	 * @param mixed $language
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
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
	 * @param mixed $language
	 *
	 * @return array
	 */
	public function insertLanguage($language) {
		return $this->insertLanguages(array($language));
	}

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function insertLanguages(array $collection) {
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
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return array
	 */
    public function listAllLanguages(array $sortOrder = null, array $limit = null) {
        return $this->listLanguages(null, $sortOrder, $limit);
    }

	/**
	 * @param array|null $filter
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function listLanguages(array $filter = null, array $sortOrder = null, array $limit = null) {
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
	 * @param array|null $filter
	 * @param array|null $sortOrder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function listTranslations(array $filter = null, array $sortOrder = null, array $limit = null){
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
	 * @param string     $domain
	 * @param array|null $sortorder
	 * @param array|null $limit
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function listTranslationsOfDomain(\string $domain, array $sortorder = null, array $limit = null){
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
	 * @param mixed $language
	 *
	 * @return array
	 */
    public function updateLanguage($language) {
        return $this->updateLanguages(array($language));
    }

	/**
	 * @param array $collection
	 *
	 * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
    public function updateLanguages(array $collection) {
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
