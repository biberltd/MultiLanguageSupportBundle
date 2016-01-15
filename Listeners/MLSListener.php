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
namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Listeners;

use BiberLtd\Bundle\CoreBundle\Core as Core;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use BiberLtd\Bundle\MultiLanguageSupportBundle\Services as MLSServices;

class MLSListener extends Core
{
    private $container;
    private $languages;
    private $ignoreList;

    /**
     * MLSListener constructor.
     *
     * @param       $container
     * @param       $kernel
     * @param array $db_options
     */
    public function __construct($container, $kernel, array $db_options = array('default', 'doctrine'))
    {
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
                    'condition' => array('column' => 'l.site', 'comparison' => '=', 'value' => $kernel->getContainer()->get('session')->get('_currentSiteId')),
                )
            )
        );
        $response = $MLSModel->listAllLanguages(array("iso_code" => "asc"));
        if (!$response->error->exist) {
            $language_codes = [];
            foreach ($response->result->set as $language) {
                $language_codes[] = $language->getIsoCode();
            }
            $this->languages = $language_codes;
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $e
     */
    public function onKernelRequest(GetResponseEvent $e)
    {
        $request = $e->getRequest();
        $path_info = ltrim($request->getPathInfo(), '/');
        $path_params = explode('/', $path_info);
        $base_url = $request->getBaseUrl();
        $host = $request->getHost();
        $protocol = 'http://';
        if ($request->isSecure()) {
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
        if (in_array($path_params, $this->ignoreList)) {
            return;
        }
        /**
         * READ COOKIE
         */
        $cookie = $this->readCookie();
        if (strlen($path_params[0]) == 2) {
            /** URI has locale in it. Therefore we check if the locale is defined within our system. */
            if (!in_array(strtolower($path_params[0]), $this->languages)) {
                /** If URI given locale is not one of our system languages then set the locale to preferred one. */
                $path_params[0] = $preferred_locale;
                $reroute = true;
            } else {
                $preferred_locale = $path_params[0];
                $this->kernel->getContainer()->get('request')->setLocale($preferred_locale);
                $this->kernel->getContainer()->get('session')->set('_locale', $preferred_locale);
            }
        } else {
            /**
             * If URI does not have locale in it; then we'll add the preferred locale to it.
             * But first we will check the cookie for language.
             */
            if (isset($cookie['locale'])) {
                $preferred_locale = $cookie['locale'];
            }
            array_unshift($path_params, $preferred_locale);
            $reroute = true;
        }
        /** Finally we create the new URL and redirect the visitor to the localized version of the page. */
        $path_info = '/' . implode('/', $path_params);
        $url = $protocol . $host . $base_url . $path_info;

        $cookie['locale'] = $preferred_locale;
        $encryptedCookie = $this->encryptCookie($cookie);
        if ($reroute) {
            /** Set cookie */
            $this->kernel->getContainer()->get('request')->setLocale($preferred_locale);
            $this->kernel->getContainer()->get('session')->set('_locale', $preferred_locale);
            /** Redirect */
            $response = new RedirectResponse($url);
            $response->headers->setCookie(new Cookie('bbr_member', $encryptedCookie));
            $e->setResponse($response);
        } else {
            setcookie('bbr_member', $encryptedCookie, null, '/');
        }
    }

    /**
     * @return array|mixed
     */
    private function readCookie()
    {
        $cookie = $this->kernel->getContainer()->get('request')->cookies;
        $enc = $this->kernel->getContainer()->get('encryption');
        $encrypted_cookie = $cookie->get('bbr_member');
        if (empty($encrypted_cookie)) {
            $cookie = [];
        } else {
            $cookie = $enc->input($encrypted_cookie)->key($this->kernel->getContainer()->getParameter('app_key'))->decrypt('enc_reversible_pkey')->output();
            $cookie = unserialize(base64_decode($cookie));
        }
        return $cookie;
    }

    /**
     * @param $cookie
     *
     * @return mixed
     */
    private function encryptCookie($cookie)
    {
        $data = base64_encode(serialize($cookie));
        $enc = $this->kernel->getContainer()->get('encryption');
        return $enc->input($data)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();
    }
}