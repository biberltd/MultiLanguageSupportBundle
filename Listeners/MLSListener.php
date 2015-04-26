<?php
/**
 * @vendor      BiberLtd
 * @package		MultilanguageSupportBundle
 * @subpackage	Services
 * @name	    MLSListener
 *
 * @author		Can Berkol
 *
 * @version     1.0.0
 * @date        26.04.2015
 *
 */

namespace BiberLtd\Bundle\MultiLanguageSupport\Listeners;
use BiberLtd\Bundle\CoreBundle\Core as Core;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use BiberLtd\Bundle\MultiLanguageSupportBundle\Services as MLSServices;

class MLSListener extends Core{
    private     $container;
    private     $languages;
	private		$ignoreList;
    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           string      $container
     * @param           array       $kernel
     * @param           array       $db_options
     */
    public function __construct($container, $kernel, $db_options = array('default', 'doctrine')){
        parent::__construct($kernel);
        $this->container = $container;
        $default_languages = $kernel->getContainer()->getParameter('languages');
        $this->languages = $default_languages;
        $this->timezone = $kernel->getContainer()->getParameter('app_timezone');
		$this->ignoreList = $kernel->getContainer()->getParameter('mls_ignore_list');
        /**
         * First, we need to try to connect language database table.
         * If the connection is successfull we will get the list of languages
         * and replace $this->languages with the list fetched from database.
         *
         * NOTE: site_id parameter must be set in app/config/paramters.yml
         */
        $MLSModel = new MLSServices\MultiLanguageSupportModel($kernel, $db_options[0], $db_options[1]);
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => 'l.site', 'comparison' => '=', 'value' => $kernel->getContainer()->getParameter('site_id')),
                )
            )
        );
        $response = $MLSModel->listAllLanguages(array("iso_code" => "asc"));
        if(!$response['error']){
            $language_codes = array();
            foreach($response['result']['set'] as $language){
                $language_codes[] = $language->getIsoCode();
            }
            $this->languages = $language_codes;
        }
    }
    /**
     * @name            __destruct()
     *                  Destructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.3.0
     *
     */
    public function __destruct(){
        foreach($this as $property => $value) {
            $this->$property = null;
        }
    }
    /**
     * @name 			onKernelRequest()
     *  				Called onKernelRequest event and handles browser language detection.
     *
     * @author          Can Berkol
     *
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param 			GetResponseEvent 	        $e
     *
     */
    public function onKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent $e){
        $request = $e->getRequest();
        $path_info = ltrim($request->getPathInfo(), '/');
        $path_params = explode('/', $path_info);
        $base_url = $request->getBaseUrl();
        $host = $request->getHost();
        $protocol = 'http://';
        if($request->isSecure()){
            $protocol = 'https://';
        }
        $preferred_locale = $request->getPreferredLanguage($this->languages);
        $reroute = false;
        /**
         * IMPORTANT
         * This script is based on a simple assumption and that is:
         *
         * The first parameter in path_info is the language code except the ignore list.
         */
        if(in_array($path_params,$this->ignoreList)){
            return;
        }
        if(strlen($path_params[0]) == 2){
            /** URI has locale in it. Therefore we check if the locale is defined within our system. */
            if(!in_array($path_params[0], $this->languages)){
                /** If URI given locale is not one of our system languages then set the locale to preferred one. */
                $path_params[0] = $preferred_locale;
                $reroute = true;
            }
            else{
                $this->kernel->getContainer()->get('request')->setLocale($path_params[0]);
                $this->kernel->getContainer()->get('session')->set('_locale', $path_params[0]);
            }
        }
        else{
            /**
             * If URI does not have locale in it; then we'll add the preferred locale to it.
             * But first we will check the cookie for language.
             */
            $cookie = $this->kernel->getContainer()->get('request')->cookies;
            $enc = $this->kernel->getContainer()->get('encryption');
            if(!isset($cookie) && !is_null($cookie)){
                $encrypted_cookie = $cookie->get('bbr_member');
                $cookie = $enc->input($encrypted_cookie)->key($this->kernel->getContainer()->getParameter('app_key'))->decrypt('enc_reversible_pkey')->output();
                $cookie = unserialize(base64_decode($cookie));
                if(isset($cookie['locale'])){
                    $preferred_locale = $cookie['locale'];
                }
            }
            array_unshift($path_params, $preferred_locale);
            $reroute = true;
        }
        /** Finally we create the new URL and redirect the visitor to the localized version of the page. */
        $path_info = '/'.implode('/', $path_params);
        $url = $protocol.$host.$base_url.$path_info;
        if($reroute){
            $this->kernel->getContainer()->get('request')->setLocale($preferred_locale);
            $this->kernel->getContainer()->get('session')->set('_locale', $preferred_locale);
            /** Redirect */
            $e->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse($url));
        }
    }
}
/**
 * Change Log
 * ****************************************
 * v1.0.0						26.04.2015
 * TW #
 * Can Berkol
 * ****************************************
 * - Class moved to MultiLanguageSupportBundle from CoreBundle.
 * - ignoreList added.
 */