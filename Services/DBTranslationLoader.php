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
     * DBTranslationLoader constructor.
     *
     * @param        $kernel
     * @param string $db_connection
     * @param string $orm
     */
    public function __construct($kernel, string $db_connection = null, string $orm = null) {
        $this->entity = array(
            'l' => array('name' => 'MultiLanguageSupportBundle:Language', 'alias' => 'l'),
            't' => array('name' => 'MultiLanguageSupportBundle:Translation', 'alias' => 't'),
            'tl' => array('name' => 'MultiLanguageSupportBundle:TranslationLocalization', 'alias' => 'tl'),
        );
        $this->dbConntection = $db_connection  ?? 'default';
        $this->orm = $orm ?? 'doctrine';
        $this->kernel = $kernel;
        $this->em = $this->kernel->getContainer()->get($this->orm)->getManager($this->dbConntection);
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
     * @param resource $resource
     * @param string      $locale
     * @param string|null $domain
     * @param int|null    $siteId
     *
     * @return bool|\Symfony\Component\Translation\MessageCatalogue
     */
    public function load($resource, string $locale, string $domain = null, int $siteId = null){
        $domain = $domain ?? 'web';
        $siteId = $siteId ?? 1;
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
            $translations = [];
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
     * @param string $phrase
     *
     * @return mixed|string
     */
    public function injectValuesIntoPlaceholders(string $phrase){
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