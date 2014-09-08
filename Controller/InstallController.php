<?php
/**
 * InstallController
 *
 * This controller is used to install default / test values to the system.
 * The controller can only be accessed from allowed IP address.
 *
 * @vendor      BiberLtd
 * @package		MultiLanguageSupportBundle
 * @subpackage	Controller
 * @name	    InstallController
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 * @date        05.08.2013
 *
 */

namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpKernel\Exception,
    Symfony\Component\HttpFoundation\Response;

class InstallController extends Controller
{
    /** @var $locale                 Holds the locale */
    protected $locale;
    /** @var $request           Holds the request object */
    protected $request;
    /** @var $session           Holds session object */
    protected $session;
    /** @var $translator        Holds the translator object */
    protected $translator;

    /**
     * @name 			init()
     *  				Each controller must call this function as its first statement.27
     *                  This function acts as a constructor and initializes default values of this controller.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     */
    protected  function init(){
        if (isset($_SERVER['HTTP_CLIENT_IP'])
            || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1', '192.168.1.134', '176.43.5.152', '192.168.1.135','192.168.1.145', '88.235.191.124'))
        ) {
            header('HTTP/1.0 403 Forbidden');
            exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
        }
        /** ****************** */
        $this->request = $this->getRequest();
        $this->session = $this->get('session');
        $this->locale = $this->request->getLocale();
        $this->translator = $this->get('translator');
    }
    /**
     * @name 			languagesAction()
     *  				DOMAIN/install/languages
     *                  Inserts detault site details into database.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     */
    public function languagesAction($current_language)
    {
        /** Initialize */
        $this->init();
        $model = $this->get('core_multi_language_support_bundle.model');

        $turkish = array(
            'name'          => 'Türkçe',
            'url_key'       => 'tr',
            'iso_code'      => 'tr',
            'schema'        => 'ltr',
            'site'          => 1
        );
        $english = array(
            'name'          => 'English',
            'url_key'       => 'en',
            'iso_code'      => 'en',
            'schema'        => 'ltr',
            'site'          => 1
        );
        $languages = array($turkish, $english);
        /**
         * Insert data into database.
         */
        $response = $model->insert_languages($languages);
        if($response['error']){
            return new Response('It seems like you have already run the install/languages command.');
        }
        $http_response = 'Default languages with the below information have been installed: <br>';
        foreach($languages as $language){
            $http_response .=
                 '<br><strong>name</strong>: '.$language['name']
                .'<br><strong>url_key</strong>: '.$language['url_key']
                .'<br><strong>iso_code</strong>: '.$language['iso_code']
                .'<br><strong>schema</strong>: '.$language['schema']
                .'<br><strong>site id</strong>: '.$language['site'];
        }

        return new Response($http_response);
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.0                      Can Berkol
 * 01.08.2013
 * **************************************
 * A Site Action
 *
 */