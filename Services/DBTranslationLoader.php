<?php
/**
 * @vendor      BiberLtd
 * @package		BiberLtd\Bundle\MultiLanguageSupportBundle
 * @subpackage	Services
 * @name	    DBTranslationLoader
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.3
 * @date        26.05.2015
 *
 */

namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Services;

use BiberLtd\Bundle\CoreBundle\CoreModel;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DBTranslationLoader implements LoaderInterface{
    private $dbConntection;
    private $em;
    private $entity;
    private $kernel;
    private $orm;
    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           object          $kernel
     * @param           string          $db_connection
     * @param           string          $orm
     */
    public function __construct($kernel, $db_connection, $orm) {
        $this->entity = array(
            'language' => array('name' => 'MultiLanguageSupportBundle:Language', 'alias' => 'l'),
            'translation' => array('name' => 'MultiLanguageSupportBundle:Translation', 'alias' => 't'),
            'translation_localization' => array('name' => 'MultiLanguageSupportBundle:TranslationLocalization', 'alias' => 'tl'),
        );
        $this->dbConntection = $db_connection;
        $this->orm = $orm;
        $this->kernel = $kernel;
        $this->em = $this->kernel->getContainer()->get($this->orm)->getManager($this->dbConntection);
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
     * @name 			load()
     *  				Translation loader load() implementation
     *
     * @since			1.0.0
     * @version         1.0.3
     * @author          Can Berkol
     *
     * @param           mixed           $resource
     * @param           string          $locale
     * @param           string          $domain
     * @param           integer         $siteId
     *
     * @return          array           $catalogue
     */
    public function load($resource, $locale, $domain = 'web', $siteId = 1){
        $mlsModel = $this->kernel->getContainer()->get('multilanguagesupport.model');
        $siteModel = $this->kernel->getContainer()->get('sitemanagement.model');
        $response = $mlsModel->getLanguage($locale, 'iso_code');
        if($response->error->exist){
            $response = $siteModel->getSite($siteId, 'id');
            if($response->error->exist){
               return false;
            }
            $site =  $response->result->set;
			$response->result->set= $site->getLanguage();
        }
        $language = $response->result->set;
        /** Grab all translations of domain */
        $response = $mlsModel->listTranslationsOfDomain($domain, array('key' => 'asc'));
        if($response->error->exist){
            $translations = array();
        }
        else{
            $translations = $response->result->set;
        }
        /** Build catalogue */
        $catalogue = new MessageCatalogue($locale);
        foreach($translations as $translation){
            /**
             * Replace special place holders.
             */
            $translationLoader = $translation->getLocalization($locale);
            if (isset($translationLoader)) {
                $phrase = $this->injectValuesIntoPlaceholders($translationLoader->getPhrase());
                $catalogue->set($translation->getKey(), $phrase, $domain);
            }
        }
        return $catalogue;
    }
    /**
     * @name 			injectValuesIntoPlaceholders()
     *  				Inject values into special place orders.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @param           string          $phrase
     *
     * @return          string
     */
    public function injectValuesIntoPlaceholders($phrase){
        if(strpos('**', $phrase) === false){
            return $phrase;
        }
        $request = $this->kernel->getContainer()->get('request');
        $host = $request->getHttpHost();
        $protocol = 'http://';
        if ($request->isSecure()) {
            $protocol = 'https://';
        }
        $url = $protocol.$host;
        if ($this->get('kernel')->getEnvironment() == 'dev') {
            $url .= '/app_dev.php';
        }

        $url .= '/'.$this->container->get('request')->getLocale();

        $placeHolders = array(
            '**SITE_URL**'      => $url
        );
        foreach($placeHolders as $key => $value){
            $phrase = str_replace($key, $value, $phrase);
        }
        return $phrase;
    }
}


/**
 * Change Log
 * **************************************
 * v1.0.3                      26.05.2015
 * Can Berkol
 * **************************************
 * BF :: $response->error->exists fixed to response->error->exist
 *
 * **************************************
 * v1.0.2                      01.05.2015
 * Can Berkol
 * **************************************
 * CR :: The class now uses ModelResponse.
 *
 * **************************************
 * v1.0.1                      Can Berkol
 * 28.04.2014
 * **************************************
 * A injectValuesIntoPlaceholders()
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 27.03.2014
 * **************************************
 * A __construct()
 * A load()
 *
 */