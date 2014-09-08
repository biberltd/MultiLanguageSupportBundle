<?php
/**
 * ManageController
 *
 * This controller is used to manage languages in database
 *
 * @package		MultiLanguageSupportBundle
 * @subpackage	Controller
 * @name	    ManageController
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 *
 */

namespace BiberLtd\Bundle\MultiLanguageSupportBundle\Controller;

use BiberLtd\Core\CoreController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpKernel\Exception,
    Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends CoreController
{
    /**
     * @name            deleteAction()
     *                  DOMAIN/{_locale}/manage/language/delete/{$singleId}
     *
     * @author          Can Berkol
     * @since           1.0.1
     * @version         1.0.1
     *
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($singleId = -1) {
        /**
         * 1. Get global services and prepare URLs
         */
        $session = $this->get('session');
        $request = $this->get('request');
        $translator = $this->get('translator');
        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');

        $this->setURLs();

        $locale = $session->get('_locale');

        /**
         * 2. Validate Access Rights
         *
         * This controller is managed and only available to non-loggedin users.
         */
        $access_map = array(
            'unmanaged' => false,
            'guest' => false,
            'authenticated' => true,
            'members' => array(),
            'groups' => array('founder', 'support', 'admin'),
            'status' => array('a')
        );
        if (!$av->has_access(null, $access_map)) {
            $sm->logAction('page.visit.fail.insufficient.rights', 1, array('route' => '/language/delete'));
            /** If already logged-in redirect back to Manage/Dashboard */
            return new RedirectResponse($this->url['base_l'] . '/manage/account/login');
        }
        /** Single Delete Mode */
        if($singleId > 0){
            $mlsModel = $this->get('multilanguagesupport.model');

            $mlsModel->deleteLanguages(array($singleId));

            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'success');
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.success.delete', array(), 'admin'));
            return new RedirectResponse($this->url['base_l'] . '/manage/language/list');
        }

        /** Multi Delete Mode */
        $form = $request->get('modalForm');

        if($form['data']['csfr'] != $session->get('_csfr')){
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'danger');
            /** $response[$code] must have a corresponding translation */
            $session->getFlashBag()->add('msg.content', $translator->trans('form.err.security.csfr', array(), 'admin'));

            return new RedirectResponse($this->url['base_l'] . '/manage/language/list');
        }

        $mlsModel = $this->get('multilanguagesupport.model');

        $toDelete = explode(',', trim($form['data']['entities'],','));

        $mlsModel->deleteLanguages($toDelete);

        $session->getFlashBag()->add('msg.status', true);
        $session->getFlashBag()->add('msg.type', 'success');
        $session->getFlashBag()->add('msg.content', $translator->trans('msg.success.delete', array(), 'admin'));
        return new RedirectResponse($this->url['base_l'] . '/manage/language/list');

    }
    /**
     * @name            editAction()
     *                  DOMAIN/{_locale}/manage/language/edit/{id}
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           integer             $id
     *
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id) {
        /**
         * 1. Get global services and prepare URLs
         */
        $session = $this->get('session');
        $translator = $this->get('translator');
        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');

        $this->setURLs();

        $locale = $session->get('_locale');
        $id = (int) $id;
        if(!is_integer($id)){
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'danger');
            /** $response[$code] must have a corresponding translation */
            $session->getFlashBag()->add('msg.content', $translator->trans('form.err.invalid.record', array(), 'admin'));
            /** csfr error */
            return new RedirectResponse($this->url['base_l'] . '/manage/camp/attribute/list');
        }

        /**
         * 2. Validate Access Rights
         *
         * This controller is managed and only available to non-loggedin users.
         */
        $access_map = array(
            'unmanaged' => false,
            'guest' => false,
            'authenticated' => true,
            'members' => array(),
            'groups' => array('founder', 'admin', 'support'),
            'status' => array('a')
        );
        if (!$av->has_access(null, $access_map)) {
            $sm->logAction('page.visit.fail.insufficient.rights', 1, array('route' => '/manage/language/edit'));
            /** If already logged-in redirect back to Manage/Dashboard */
            return new RedirectResponse($this->url['base_l'] . '/manage/account/login');
        }
        $flash = $this->prepareFlash($session);
        /**
         * 3. OPTIONAL :: ADDITIONAL PROCESSINGS
         */
        /** Get current language */
        $mlsModel = $this->get('multilanguagesupport.model');
        $response = $mlsModel->getLanguage($locale, 'iso_code');
        $language = false;
        if(!$response['error']){
            $language = $response['result']['set'];
        }
        unset($response);

        /** Get site */
        $siteModel = $this->get('sitemanagement.model');
        $response = $siteModel->getSite(1, 'id');
        $site = false;
        if(!$response['error']){
            $site = $response['result']['set'];
        }
        unset($response);
        /** Get page */
        $cmsModel = $this->get('cms.model');
        $pageCode = 'cmsLanguageEdit'; /** @deprecated pageCode, use cmsEditLanguage instead */
        $response = $cmsModel->getPage($pageCode, 'code');
        if ($response['error']) {
            $pageCode = 'cmsEditLanguage';
            $response = $cmsModel->getPage($pageCode, 'code');
            if($response['error']){
                $sm->logAction('page.visit.fail.404', 1, array('route' => '/manage/language/edit'));
                /** If page not found, redirect to 404 page. */
                return new RedirectResponse($this->url['manage'].'/error/404');
            }

        }
        $sm->logAction('page.visit', 1, array('route' => '/manage/language/edit'));
        $current_page = $response['result']['set'];
        unset($response);

        $core = array(
            'locale'    => $this->get('session')->get('_locale'),
            'kernel'    => $this->get('kernel'),
            'theme'     => $current_page->getLayout()->getTheme()->getFolder(),
            'url'       => $this->url,
        );

        /** Get Page modules grouped by Section */
        $response = $cmsModel->listModulesOfPageLayoutsGroupedBySection($current_page, array('sort_order' => 'asc'));
        if ($response['error']) {
            /** Show error if no modules can be loaded */
            echo $translator->trans('error.page.load', array(), 'sys');
            exit;
        }
        $blocks = $response['result']['set'];
        unset($response);

        /**
         * Get core render model and prepare core information
         */
        $coreRender = $this->get('corerender.model');

        /** Get top navigation */
        $response = $cmsModel->listItemsOfNavigation('cms_nav_top', 'top', array('sort_order' => 'asc'));
        $topNavItems = array();
        if(!$response['error']){
            $topNavItems = $response['result']['set'];
        }
        $topNavigation = $coreRender->renderQuickActionsNavigation($topNavItems, $core);
        unset($topNavItems, $response);

        /** Get project logo */
        $siteSettings = json_decode($site->getSettings());
        $projectLogoUrl = $this->url['cdn'].'/site/logo/'.$siteSettings->logo;
        $dashboardSettings = array(
            'link'  => $this->url['base_l'].'/manage/dashboard',
            'title' => $translator->trans('dashboard.title', array(), 'admin'),
        );
        $renderedProjectLogo = $coreRender->renderProjectLogo($projectLogoUrl, $site->getTitle(), $core, $dashboardSettings);
        unset($siteSettings, $projectLogoUrl, $dashboardSettings);

        /** Prepare sidebar separator */
        $renderedSidebarSeparator = $coreRender->renderSidebarSeparator($core);

        /** Get sidebar navigation */
        $response = $cmsModel->listItemsOfNavigation('cms_nav_main', 'top', array('sort_order' => 'asc'));
        $sideNavItems = array();
        if(!$response['error']){
            $sideNavItems = $response['result']['set'];
        }
        unset($response);
        $navCollection = array();
        foreach($sideNavItems as $navItem){
            $response = $cmsModel->listNavigationItemsOfParent($navItem, array('sort_order' => 'asc'));
            $childItems = array();
            $hasChildren = false;
            $selectedParent = false;
            if(!$response['error']){
                $hasChildren = true;
                foreach($response['result']['set'] as $childItem){
                    $childNavSelected = false;
                    if($childItem->getPage()->getId() == $current_page->getId()){
                        $childNavSelected = true;
                        $selectedParent = $childItem->getParent()->getId();
                    }
                    $childItems[] = array(
                        'entity'  => $childItem,
                        'selected'=> $childNavSelected,
                    );
                }
            }
            $navSelected = false;
            if($navItem->getId() == $selectedParent || $navItem->getLocalization($locale)->getUrlKey() == '/manage/language'){
                $navSelected = true;
            }
            $navCollection[]  = array(
                'children'      => $childItems,
                'code'          => time(),
                'entity'        => $navItem,
                'hasChildren'   => $hasChildren,
                'selected'      => $navSelected,
            );
            unset($response, $childItems);
        }
        unset($sideNavItems);

        $renderedSidebarNavigation = $coreRender->renderSidebarNavigation($navCollection, $core);

        /** Language details */
        $response = $mlsModel->getLanguage($id, 'id');
        if($response['error']){
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'danger');
            /** $response[$code] must have a corresponding translation */
            $session->getFlashBag()->add('msg.content', $translator->trans('form.err.invalid.record', array(), 'admin'));
            /** csfr error */
            return new RedirectResponse($this->url['base_l'] . '/manage/language/list');
        }
        $currentLang = $response['result']['set'];
        $ltrSelected = false;
        $rtlSelected = false;
        switch($currentLang->getSchema()){
            case 'ltr':
                $ltrSelected = true;
                break;
            case 'rtl':
                $rtlSelected = true;
                break;
        }
        $widgetTitle = $translator->trans('widget.title.edit.language', array(), 'admin') != 'widget.title.edit.language' ? $translator->trans('widget.title.edit.language', array(), 'admin') : $translator->trans('title.language.detail', array(), 'admin');

        $widgetSettings = array(
            'actionsEnabled'        => true,
            'mainFormEnabled'       => true,
            'size'                  => 12,
            'wrapInRow'             => true,
        );
        $widgetActions = array(
            array(
                'buttonType'        => 'primary',
                'classes'           => array('pull-right'),
                'id'                => 'save',
                'name'              => $translator->trans('form.btn.save', array(), 'admin') != 'form.btn.save' ? $translator->trans('form.btn.save', array(), 'admin') : $translator->trans('btn.save', array(), 'admin'),
                'type'              => 'button',
            ),
            array(
                'buttonType'        => 'danger',
                'classes'           => array('pull-left'),
                'id'                => 'delete',
                'link'              => '#modal-delete',
                'modal'             => array(
                    'btn'           => array(
                        array(
                            'dismiss'   => true,
                            'link'      => '',
                            'name'      => $translator->trans('form.btn.cancel', array(), 'admin') != 'form.btn.cancel' ? $translator->trans('form.btn.cancel', array(), 'admin') : $translator->trans('btn.cancel', array(), 'admin'),
                            'purpose'   => 'cancel',
                            'style'     => 'button',
                            'type'      => 'button',
                        ),
                        array(
                            'dismiss'   => false,
                            'link'      => $this->url['base_l'].'/manage/language/delete/'.$currentLang->getId(),
                            'name'      => $translator->trans('form.btn.confirm', array(), 'admin') != 'form.btn.confirm' ? $translator->trans('form.btn.confirm', array(), 'admin') : $translator->trans('btn.confirm', array(), 'admin'),
                            'purpose'   => 'confirm',
                            'style'     => 'primary',
                            'type'      => 'a',
                        ),
                    ),
                    'msg'               => $translator->trans('modal.delete.msg', array(), 'admin') != 'modal.delete.msg' ? $translator->trans('modal.delete.msg', array(), 'admin') : $translator->trans('msg.prompt.confirm.delete.record', array(), 'admin'),
                    'title'             => $translator->trans('modal.delete.title', array(), 'admin') != 'modal.delete.title' ? $translator->trans('modal.delete.title', array(), 'admin') : $translator->trans('title.language.detail', array(), 'admin'),
                ),
                'name'              => $translator->trans('form.btn.delete', array(), 'admin') != 'form.btn.delete' ? $translator->trans('form.btn.delete', array(), 'admin') : $translator->trans('btn.delete', array(), 'admin'),
                'type'              => 'button',
            ),
        );
        $response = $siteModel->listSites(null, array('title' => 'desc'));
        $siteOptions = array();
        if(!$response['error']){
            foreach($response['result']['set'] as $aSite){
                $option = array(
                    'selected'      => false,
                    'name'          => $aSite->getTitle(),
                    'value'         => $aSite->getId(),
                );
                $siteOptions[] = $option;
            }
        }
        $selectedA = false;
        $selectedI = true;
        if($currentLang->getStatus() == 'a'){
            $selectedA = true;
            $selectedI = false;
        }
        $statusOptions = array(
            array('selected' => $selectedA, 'value' => 'a', 'name' => $translator->trans('lbl.active', array(), 'admin')),
            array('selected' => $selectedI, 'value' => 'i', 'name' => $translator->trans('lbl.inactive', array(), 'admin')),
        );
        $widgetContent = array(
            array(
                'form'      => array(
                    'rows'      => array(
                        array(
                            'size'          => 12,
                            'inputs'            => array(
                                array(
                                    'attributes'=> array('required'),
                                    'id'        => 'name',
                                    'label'     => $translator->trans('form.lbl.name.item', array(), 'admin') != 'form.lbl.name.item' ? $translator->trans('form.lbl.name.item', array(), 'admin') : $translator->trans('lbl.name.item', array(), 'admin'),
                                    'name'      => 'name',
                                    'size'      => 12,
                                    'settings'  => array('wrapInRow' => true, 'rowSize' => 6),
                                    'type'      => 'textInput',
                                    'value'     => $currentLang->getName(),
                                ),
                                array(
                                    'attributes'=> array('required'),
                                    'id'        => 'iso_code',
                                    'label'     => $translator->trans('form.lbl.iso_code', array(), 'admin') != 'form.lbl.iso_code' ? $translator->trans('form.lbl.iso_code', array(), 'admin') : $translator->trans('lbl.iso_code', array(), 'admin'),
                                    'name'      => 'iso_code',
                                    'size'      => 12,
                                    'settings'  => array('wrapInRow' => true, 'rowSize' => 6),
                                    'type'      => 'textInput',
                                    'value'     => $currentLang->getIsoCode(),
                                ),
                            ),
                        ),
                        array(
                            'size'          => 12,
                            'inputs'            => array(
                                array(
                                    'id'        => 'schema',
                                    'label'     => $translator->trans('form.lbl.schema', array(), 'admin') != 'form.lbl.schema' ? $translator->trans('form.lbl.schema', array(), 'admin') : $translator->trans('lbl.schema', array(), 'admin'),
                                    'name'      => 'schema',
                                    'size'      => 12,
                                    'settings'  => array('wrapInRow' => true, 'rowSize' => 6),
                                    'type'      => 'dropDown',
                                    'options'   => array(
                                        array(
                                            'name'      => $translator->trans('form.lbl.ltr', array(), 'admin') != 'form.lbl.ltr' ? $translator->trans('form.lbl.ltr', array(), 'admin') : $translator->trans('lbl.ltr', array(), 'admin'),
                                            'selected'  => $ltrSelected,
                                            'value'     => 'ltr',
                                        ),
                                        array(
                                            'name'      => $translator->trans('form.lbl.rtl', array(), 'admin') != 'form.lbl.rtl' ? $translator->trans('form.lbl.rtl', array(), 'admin') : $translator->trans('lbl.rtl', array(), 'admin'),
                                            'selected'  => $rtlSelected,
                                            'value'     => 'rtl',
                                        ),
                                    ),
                                ),
                                array(
                                    'id'        => 'site',
                                    'label'     => $translator->trans('form.lbl.site', array(), 'admin') != 'form.lbl.site' ? $translator->trans('form.lbl.site', array(), 'admin') : $translator->trans('lbl.site', array(), 'admin'),
                                    'name'      => 'site',
                                    'size'      => 12,
                                    'settings'  => array('wrapInRow' => true, 'rowSize' => 6),
                                    'type'      => 'dropDown',
                                    'options'   => $siteOptions,
                                ),
                            ),
                        ),
                        array(
                            'size'          => 12,
                            'inputs'            => array(
                                array(
                                    'id'        => 'status',
                                    'label'     => $translator->trans('lbl.status', array(), 'admin'),
                                    'name'      => 'status',
                                    'size'      => 12,
                                    'settings'  => array('wrapInRow' => true, 'rowSize' => 12),
                                    'type'      => 'dropDown',
                                    'options'   => $statusOptions,
                                ),
                            ),
                        ),
                    ),
                ),
                'groupCode' => 'langDetail',
            ),
        );
        $widgetIcon = $this->url['domain'].'/themes/'.$current_page->getLayout()->getTheme()->getFolder().'/img/icons/light-sh/flag.png';
        $renderedLanguageForm = $coreRender->renderGenericFormWidget($widgetActions, $widgetContent, $core, $widgetTitle, $widgetIcon, $widgetSettings);
        unset($inputs, $icon);

        /**
         * 4. REQUIRED :: PREPARE TEMPLATE TAGS
         */
        $vars = array(
            'entity_id' => $currentLang->getId(),
            'flash' => $flash,
            'page' => array(
                'blocks' => $blocks,
                'entity' => $current_page,
                'form'   => array(
                    'action'    => $this->url['base_l'].'/manage/language/process/edit/'.$currentLang->getId(),
                    'csfr'      => $this->generateCSFR($session),
                    'method'    => 'post',
                ),
                'meta' => array(
                    'description' => $current_page->getLocalization($locale)->getMetaDescription(),
                    'keywords' => $current_page->getLocalization($locale)->getMetaKeywords(),
                    'title' => $current_page->getLocalization($locale)->getTitle(),
                ),
            ),
            'renderedLanguageForm' => $renderedLanguageForm,
            'renderedProjectLogo' => $renderedProjectLogo,
            'renderedSidebarNavigation' => $renderedSidebarNavigation,
            'renderedSidebarSeparator' => $renderedSidebarSeparator,
            'renderedTopNavigation' => $topNavigation,
            'site' => array(
                'entity' => $site,
                'name' => $site->getTitle(),
            ),
            'style' => array(
                'body' => array(
                    'classes' => array(),
                ),
            ),
            'xssCode' => $this->generateXssCode(),
        );

        $css = array('css/style.css', 'css/bootstrap-switch.css');
        $js = array(
            'js/libs/modernizr-2.6.2.min.js',
            'js/libs/jquery-1.10.2.js',
            'js/libs/json2.js',
            'js/libs/bootstrap.js',
            'js/plugins/validate/jquery.validate.1.11.1.js',
            'js/plugins/collapsible/collapsible.js',
            'js/plugins/switch/bootstrap-switch.min.js',
            'js/plugins/form2js/form2js.js',
            'js/plugins/form2js/jquery.toObject.js',
            'js/plugins/form2js/js2form.js'
        );

        /**
         * 5. REQUIRED :: MERGE PREPARED TAGS WITH DEFAULTS
         */
        $tags = $this->initDefaults($css, $js, $vars, $current_page->getLayout()->getTheme()->getFolder());

        /**
         * 6. REQUIRED :: RENDER PAGE
         *      note that if you do not want to immediately render the view to browser you need to use
         *      renderView() method instead of render() method.
         */
        return $this->render($current_page->getBundleName().':'.$current_page->getLayout()->getTheme()->getFolder().'/Pages:p_'.$pageCode.'.html.smarty', $tags);
    }
    /**
     * @name            listAction()
     *                  DOMAIN/{_locale}/manage/language/list
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function listAction() {
        /**
         * 1. Get global services and prepare URLs
         */
        $session = $this->get('session');
        $translator = $this->get('translator');
        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');

        $this->setURLs();

        $locale = $session->get('_locale');

        /**
         * 2. Validate Access Rights
         *
         * This controller is managed and only available to non-loggedin users.
         */
        $access_map = array(
            'unmanaged' => false,
            'guest' => false,
            'authenticated' => true,
            'members' => array(),
            'groups' => array('founder', 'admin', 'support'),
            'status' => array('a')
        );
        if (!$av->has_access(null, $access_map)) {
            $sm->logAction('page.visit.fail.insufficient.rights', 1, array('route' => '/manage/language/list'));
            /** If already logged-in redirect back to Manage/Dashboard */
            return new RedirectResponse($this->url['base_l'] . '/manage/account/login');
        }
        $flash = $this->prepareFlash($session);
        /**
         * 3. OPTIONAL :: ADDITIONAL PROCESSINGS
         */
        /** Get current language */
        $mlsModel = $this->get('multilanguagesupport.model');
        $response = $mlsModel->getLanguage($locale, 'iso_code');
        $language = false;
        if(!$response['error']){
            $language = $response['result']['set'];
        }
        unset($response);

        /** Get site */
        $siteModel = $this->get('sitemanagement.model');
        $response = $siteModel->getSite(1, 'id');
        $site = false;
        if(!$response['error']){
            $site = $response['result']['set'];
        }
        unset($response);
        /** Get page */
        $cmsModel = $this->get('cms.model');
        $pageCode = 'cmsLanguageList'; /** @deprecated pageCode, use cmsListLanguages instead */
        $response = $cmsModel->getPage($pageCode, 'code');
        if ($response['error']) {
            $pageCode = 'cmsListLanguages';
            $response = $cmsModel->getPage($pageCode, 'code');
            if($response['error']){
                $sm->logAction('page.visit.fail.404', 1, array('route' => '/manage/language/list'));
                /** If page not found, redirect to 404 page. */
                return new RedirectResponse($this->url['manage'].'/error/404');
            }
        }
        $sm->logAction('page.visit', 1, array('route' => '/manage/language/list'));
        $current_page = $response['result']['set'];
        unset($response);

        /** Get Page modules grouped by Section */
        $response = $cmsModel->listModulesOfPageLayoutsGroupedBySection($current_page, array('sort_order' => 'asc'));
        if ($response['error']) {
            /** Show error if no modules can be loaded */
            echo $translator->trans('error.page.load', array(), 'sys');
            exit;
        }
        $blocks = $response['result']['set'];
        unset($response);

        /**
         * Get core render model and prepare core information
         */
        $coreRender = $this->get('corerender.model');
        $core = array(
            'locale' => $this->get('session')->get('_locale'),
            'theme' => $current_page->getLayout()->getTheme()->getFolder(),
            'url' => $this->url,
        );

        /** Get top navigation */
        $response = $cmsModel->listItemsOfNavigation('cms_nav_top', 'top', array('sort_order' => 'asc'));
        $topNavItems = array();
        if(!$response['error']){
            $topNavItems = $response['result']['set'];
        }
        $topNavigation = $coreRender->renderQuickActionsNavigation($topNavItems, $core);
        unset($topNavItems, $response);

        /** Get project logo */
        $siteSettings = json_decode($site->getSettings());
        $projectLogoUrl = $this->url['cdn'].'/site/logo/'.$siteSettings->logo;
        $dashboardSettings = array(
            'link'  => $this->url['base_l'].'/manage/dashboard',
            'title' => $translator->trans('dashboard.title', array(), 'admin'),
        );
        $renderedProjectLogo = $coreRender->renderProjectLogo($projectLogoUrl, $site->getTitle(), $core, $dashboardSettings);
        unset($siteSettings, $projectLogoUrl, $dashboardSettings);

        /** Prepare sidebar separator */
        $renderedSidebarSeparator = $coreRender->renderSidebarSeparator($core);

        /** Get sidebar navigation */
        $response = $cmsModel->listItemsOfNavigation('cms_nav_main', 'top', array('sort_order' => 'asc'));
        $sideNavItems = array();
        if(!$response['error']){
            $sideNavItems = $response['result']['set'];
        }
        unset($response);
        $navCollection = array();
        foreach($sideNavItems as $navItem){
            $response = $cmsModel->listNavigationItemsOfParent($navItem, array('sort_order' => 'asc'));
            $childItems = array();
            $hasChildren = false;
            $selectedParent = false;
            if(!$response['error']){
                $hasChildren = true;
                foreach($response['result']['set'] as $childItem){
                    $childNavSelected = false;
                    if($childItem->getPage()->getId() == $current_page->getId()){
                        $childNavSelected = true;
                        $selectedParent = $childItem->getParent()->getId();
                    }
                    $childItems[] = array(
                        'entity'  => $childItem,
                        'selected'=> $childNavSelected,
                    );
                }
            }
            $navSelected = false;
            if($navItem->getId() == $selectedParent){
                $navSelected = true;
            }
            $navCollection[]  = array(
                'children'      => $childItems,
                'code'          => time(),
                'entity'        => $navItem,
                'hasChildren'   => $hasChildren,
                'selected'      => $navSelected,
            );
            unset($response, $childItems);
        }
        unset($sideNavItems);

        $renderedSidebarNavigation = $coreRender->renderSidebarNavigation($navCollection, $core);

        /** GET Languages */
        $response = $mlsModel->listAllLanguages(array('name' => 'desc'));
        $languages = array();
        if(!$response['error']){
            $languages = $response['result']['set'];
        }
        /** Render Data Table */
        $dtTitle = $translator->trans('widget.title.list.languages', array(), 'admin') != 'widget.title.list.languages' ? $translator->trans('widget.title.list.languages', array(), 'admin') : $translator->trans('title.language.list', array(), 'admin');
        $dtSettings = array(
            'ajax'          => false,
            'editable'      => true,
        );
        $dtData = array();
        $dtHeaders = array(
            array('code' => 'id', 'name' =>  $translator->trans('form.lbl.id', array(), 'admin') != 'form.lbl.id' ? $translator->trans('form.lbl.id', array(), 'admin') : $translator->trans('lbl.id', array(), 'admin')),
            array('code' => 'name', 'name' =>  $translator->trans('form.lbl.name.item', array(), 'admin') != 'form.lbl.name.item' ? $translator->trans('form.lbl.name.item', array(), 'admin') : $translator->trans('lbl.name.item', array(), 'admin')),
            array('code' => 'isoCode', 'name' =>  $translator->trans('form.lbl.iso_code', array(), 'admin') != 'form.lbl.iso_code' ? $translator->trans('form.lbl.iso_code', array(), 'admin') : $translator->trans('lbl.iso_code', array(), 'admin')),
            array('code' => 'status', 'name' =>  $translator->trans('lbl.status', array(), 'admin')),
            array('code' => 'action', 'name' => ''),
        );
        $dtItems = array();
        $editTxt = $translator->trans('form.btn.edit', array(), 'admin') != 'form.btn.edit' ? $translator->trans('form.btn.edit', array(), 'admin') : $translator->trans('btn.edit', array(), 'admin');
        foreach($languages as $lang){
            $item = new \stdClass();
            $item->DbId = $lang->getId();
            $item->id = $lang->getId();
            $item->name = $lang->getName();
            $item->isoCode = $lang->getIsoCode();
            $item->action = '<a href="'.$this->url['base_l'].'/manage/language/edit/'.$lang->getId().'">'.$editTxt.'</a>';
            $item->status = $translator->trans('lbl.inactive', array(), 'admin');
            if($lang->getStatus() == 'a'){
                $item->status = $translator->trans('lbl.active', array(), 'admin');
            }
            $dtItems[] = $item;
        }
        $dtData['headers'] = $dtHeaders;
        $dtData['items'] = $dtItems;
        $dtData['options'] = array(
            array('name' => $translator->trans('form.lbl.delete', array(), 'admin') != 'form.lbl.delete' ? $translator->trans('form.lbl.delete', array(), 'admin') : $translator->trans('lbl.delete', array(), 'admin'), 'value' => 'delete'),
        );
        $dtData['modals'] = array(
            array(
                'btn'   => array(
                    'cancel'    => $translator->trans('form.btn.cancel', array(), 'admin') != 'form.btn.cancel' ? $translator->trans('form.lbl.delete', array(), 'admin') : $translator->trans('btn.cancel', array(), 'admin'),
                    'confirm'   => $translator->trans('form.btn.confirm', array(), 'admin') != 'form.btn.confirm' ? $translator->trans('form.lbl.delete', array(), 'admin') : $translator->trans('btn.confirm', array(), 'admin'),
                ),
                'id'    => 'delete',
                'msg'   => $translator->trans('modal.delete.msg', array(), 'admin') != 'modal.delete.msg' ? $translator->trans('modal.delete.msg', array(), 'admin') : $translator->trans('msg.prompt.confirm.delete.record', array(), 'admin'),
                'title' => $translator->trans('modal.delete.title', array(), 'admin') != 'modal.delete.title' ? $translator->trans('modal.delete.title', array(), 'admin') : $translator->trans('title.confirm.delete', array(), 'admin'),
            ),
        );
        $dtTxt = array(
            'btn' => array(
                'edit' => $translator->trans('btn.edit', array(), 'datatable'),
            ),
            'lbl' => array(
                'find' => $translator->trans('lbl.find', array(), 'datatable'),
                'first' => $translator->trans('lbl.first', array(), 'datatable'),
                'info' => $translator->trans('lbl.info', array(), 'datatable'),
                'last' => $translator->trans('lbl.last', array(), 'datatable'),
                'limit' => $translator->trans('lbl.limit', array(), 'datatable'),
                'next' => $translator->trans('lbl.next', array(), 'datatable'),
                'prev' => $translator->trans('lbl.prev', array(), 'datatable'),
                'processing' => $translator->trans('lbl.processing', array(), 'datatable'),
                'recordNotFound' => $translator->trans('lbl.not_found', array(), 'datatable'),
                'noRecords' => $translator->trans('lbl.no_records', array(), 'datatable'),
                'numberOfRecords' => $translator->trans('lbl.number_of_records', array(), 'datatable'),
            ),
        );
        $renderedLanguageDataTable = $coreRender->renderDataTable($dtData, $core, $dtTitle, $dtTxt, $dtSettings);

        /**
         * 4. REQUIRED :: PREPARE TEMPLATE TAGS
         */
        $vars = array(
            'flash' => $flash,
            'link' => $this->url,
            'modal' => array(
                'form' => array(
                    'action'    => $this->url['base_l'].'/manage/language/delete',
                    'csfr'      => $this->generateCSFR($session),
                    'method'    => 'post',
                ),
            ),
            'page' => array(
                'blocks' => $blocks,
                'entity' => $current_page,
                'form'  => array(
                    'action'    => $this->url['base_l'].'/manage/language/delete',
                    'csfr'      => $this->generateCSFR($session),
                    'method'    => 'post',
                ),
                'meta' => array(
                    'description' => $current_page->getLocalization($locale)->getMetaDescription(),
                    'keywords' => $current_page->getLocalization($locale)->getMetaKeywords(),
                    'title' => $current_page->getLocalization($locale)->getTitle(),
                ),
            ),
            'renderedLanguageDataTable' => $renderedLanguageDataTable ,
            'renderedProjectLogo' => $renderedProjectLogo,
            'renderedSidebarNavigation' => $renderedSidebarNavigation,
            'renderedSidebarSeparator' => $renderedSidebarSeparator,
            'renderedTopNavigation' => $topNavigation,
            'site' => array(
                'entity' => $site,
                'name' => $site->getTitle(),
            ),
            'style' => array(
                'body' => array(
                    'classes' => array(),
                ),
            ),
            'xssCode' => $this->generateXssCode(),
        );

        $css = array('css/style.css');
        $js = array(
            'js/libs/jquery-1.10.2.js',
            'js/plugins/validate/jquery.validate.1.11.1.js',
            'js/libs/bootstrap.js',
            'js/plugins/collapsible/collapsible.js',
            'js/plugins/datatable/datatable.js'
        );
        /**
         * 5. REQUIRED :: MERGE PREPARED TAGS WITH DEFAULTS
         */
        $tags = $this->initDefaults($css, $js, $vars, $current_page->getLayout()->getTheme()->getFolder());

        /**
         * 6. REQUIRED :: RENDER PAGE
         *      note that if you do not want to immediately render the view to browser you need to use
         *      renderView() method instead of render() method.
         */
        return $this->render($current_page->getBundleName().':'.$current_page->getLayout()->getTheme()->getFolder().'/Pages:p_'.$pageCode.'.html.smarty', $tags);
    }
    /**
     * @name            newAction()
     *                  DOMAIN/{_locale}/manage/language/new
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function newAction() {
        /**
         * 1. Get global services and prepare URLs
         */
        $session = $this->get('session');
        $translator = $this->get('translator');
        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');

        $this->setURLs();

        $locale = $session->get('_locale');

        /**
         * 2. Validate Access Rights
         *
         * This controller is managed and only available to non-loggedin users.
         */
        $access_map = array(
            'unmanaged' => false,
            'guest' => false,
            'authenticated' => true,
            'members' => array(),
            'groups' => array('founder', 'admin', 'support'),
            'status' => array('a')
        );
        if (!$av->has_access(null, $access_map)) {
            $sm->logAction('page.visit.fail.insufficient.rights', 1, array('route' => '/manage/language/new'));
            /** If already logged-in redirect back to Manage/Dashboard */
            return new RedirectResponse($this->url['base_l'] . '/manage/account/login');
        }
        $flash = $this->prepareFlash($session);
        /**
         * 3. OPTIONAL :: ADDITIONAL PROCESSINGS
         */
        /** Get current language */
        $mlsModel = $this->get('multilanguagesupport.model');
        $response = $mlsModel->getLanguage($locale, 'iso_code');
        $language = false;
        if(!$response['error']){
            $language = $response['result']['set'];
        }
        unset($response);

        /** Get site */
        $siteModel = $this->get('sitemanagement.model');
        $response = $siteModel->getSite(1, 'id');
        $site = false;
        if(!$response['error']){
            $site = $response['result']['set'];
        }
        unset($response);
        /** Get page */
        $cmsModel = $this->get('cms.model');
        $pageCode = 'cmsLanguageAdd'; /** @deprecated pageCode, use cmsNewLanguage instead */
        $response = $cmsModel->getPage($pageCode, 'code');
        if ($response['error']) {
            $pageCode = 'cmsNewLanguage';
            $response = $cmsModel->getPage($pageCode, 'code');
            if($response['error']){
                $sm->logAction('page.visit.fail.404', 1, array('route' => '/manage/language/new/'.$pageCode));
                /** If page not found, redirect to 404 page. */
                return new RedirectResponse($this->url['manage'].'/error/404');
            }
        }
        $sm->logAction('page.visit', 1, array('route' => '/manage/language/new'));
        $current_page = $response['result']['set'];
        unset($response);

        $core = array(
            'locale'    => $this->get('session')->get('_locale'),
            'kernel'    => $this->get('kernel'),
            'theme'     => $current_page->getLayout()->getTheme()->getFolder(),
            'url'       => $this->url,
        );

        /** Get Page modules grouped by Section */
        $response = $cmsModel->listModulesOfPageLayoutsGroupedBySection($current_page, array('sort_order' => 'asc'));
        if ($response['error']) {
            /** Show error if no modules can be loaded */
            echo $translator->trans('error.page.load', array(), 'sys');
            exit;
        }
        $blocks = $response['result']['set'];
        unset($response);

        /**
         * Get core render model and prepare core information
         */
        $coreRender = $this->get('corerender.model');

        /** Get top navigation */
        $response = $cmsModel->listItemsOfNavigation('cms_nav_top', 'top', array('sort_order' => 'asc'));
        $topNavItems = array();
        if(!$response['error']){
            $topNavItems = $response['result']['set'];
        }
        $topNavigation = $coreRender->renderQuickActionsNavigation($topNavItems, $core);
        unset($topNavItems, $response);

        /** Get project logo */
        $siteSettings = json_decode($site->getSettings());
        $projectLogoUrl = $this->url['cdn'].'/site/logo/'.$siteSettings->logo;
        $dashboardSettings = array(
            'link'  => $this->url['base_l'].'/manage/dashboard',
            'title' => $translator->trans('dashboard.title', array(), 'admin'),
        );
        $renderedProjectLogo = $coreRender->renderProjectLogo($projectLogoUrl, $site->getTitle(), $core, $dashboardSettings);
        unset($siteSettings, $projectLogoUrl, $dashboardSettings);

        /** Prepare sidebar separator */
        $renderedSidebarSeparator = $coreRender->renderSidebarSeparator($core);

        /** Get sidebar navigation */
        $response = $cmsModel->listItemsOfNavigation('cms_nav_main', 'top', array('sort_order' => 'asc'));
        $sideNavItems = array();
        if(!$response['error']){
            $sideNavItems = $response['result']['set'];
        }
        unset($response);
        $navCollection = array();
        foreach($sideNavItems as $navItem){
            $response = $cmsModel->listNavigationItemsOfParent($navItem, array('sort_order' => 'asc'));
            $childItems = array();
            $hasChildren = false;
            $selectedParent = false;
            if(!$response['error']){
                $hasChildren = true;
                foreach($response['result']['set'] as $childItem){
                    $childNavSelected = false;
                    if($childItem->getPage()->getId() == $current_page->getId()){
                        $childNavSelected = true;
                        $selectedParent = $childItem->getParent()->getId();
                    }
                    $childItems[] = array(
                        'entity'  => $childItem,
                        'selected'=> $childNavSelected,
                    );
                }
            }
            $navSelected = false;
            if($navItem->getId() == $selectedParent){
                $navSelected = true;
            }
            $navCollection[]  = array(
                'children'      => $childItems,
                'code'          => time(),
                'entity'        => $navItem,
                'hasChildren'   => $hasChildren,
                'selected'      => $navSelected,
            );
            unset($response, $childItems);
        }
        unset($sideNavItems);

        $renderedSidebarNavigation = $coreRender->renderSidebarNavigation($navCollection, $core);

        /** Language details */
        $widgetTitle = $translator->trans('widget.title.edit.language', array(), 'admin') != 'widget.title.edit.language' ? $translator->trans('widget.title.edit.language', array(), 'admin') : $translator->trans('title.language.detail', array(), 'admin');

        $widgetSettings = array(
            'actionsEnabled'        => true,
            'mainFormEnabled'       => true,
            'size'                  => 12,
            'wrapInRow'             => true,
        );
        $widgetActions = array(
            array(
                'buttonType'        => 'primary',
                'classes'            => array('pull-right'),
                'id'                => 'save',
                'name'              => $translator->trans('form.btn.save', array(), 'admin') != 'form.btn.save' ? $translator->trans('form.btn.save', array(), 'admin') : $translator->trans('btn.save', array(), 'admin'),
                'type'              => 'button',
            ),
        );
        $response = $siteModel->listSites(null, array('title' => 'desc'));
        $siteOptions = array();
        if(!$response['error']){
            foreach($response['result']['set'] as $aSite){
                $option = array(
                    'selected'      => false,
                    'name'          => $aSite->getTitle(),
                    'value'         => $aSite->getId(),
                );
                $siteOptions[] = $option;
            }
        }
        $statusOptions = array(
            array('selected' => false, 'value' => 'a', 'name' => $translator->trans('lbl.active', array(), 'admin')),
            array('selected' => false, 'value' => 'i', 'name' => $translator->trans('lbl.inactive', array(), 'admin')),
        );
        $widgetContent = array(
            array(
                'form'      => array(
                    'rows'      => array(
                        array(
                            'size'          => 12,
                            'inputs'            => array(
                                    array(
                                        'attributes'=> array('required'),
                                        'id'        => 'name',
                                        'label'     => $translator->trans('form.lbl.name.item', array(), 'admin') != 'form.lbl.name.item' ? $translator->trans('form.lbl.name.item', array(), 'admin') : $translator->trans('lbl.name.item', array(), 'admin'),
                                        'name'      => 'name',
                                        'size'      => 12,
                                        'settings'  => array('wrapInRow' => true, 'rowSize' => 6),
                                        'type'      => 'textInput',
                                        'value'     => '',
                                    ),
                                    array(
                                        'attributes'=> array('required'),
                                        'id'        => 'iso_code',
                                        'label'     => $translator->trans('form.lbl.iso_code', array(), 'admin') != 'form.lbl.iso_code' ? $translator->trans('form.lbl.iso_code', array(), 'admin') : $translator->trans('lbl.iso_code', array(), 'admin'),
                                        'name'      => 'iso_code',
                                        'size'      => 12,
                                        'settings'  => array('wrapInRow' => true, 'rowSize' => 6),
                                        'type'      => 'textInput',
                                        'value'     => '',
                                    ),
                            ),
                        ),
                        array(
                            'size'          => 12,
                            'inputs'            => array(
                                array(
                                    'id'        => 'schema',
                                    'label'     => $translator->trans('form.lbl.schema', array(), 'admin') != 'form.lbl.schema' ? $translator->trans('form.lbl.schema', array(), 'admin') : $translator->trans('lbl.schema', array(), 'admin'),
                                    'name'      => 'schema',
                                    'size'      => 12,
                                    'settings'  => array('wrapInRow' => true, 'rowSize' => 6),
                                    'type'      => 'dropDown',
                                    'options'   => array(
                                        array(
                                            'name'      => $translator->trans('form.lbl.ltr', array(), 'admin') != 'form.lbl.ltr' ? $translator->trans('form.lbl.ltr', array(), 'admin') : $translator->trans('lbl.ltr', array(), 'admin'),
                                            'selected'  => true,
                                            'value'     => 'ltr',
                                        ),
                                        array(
                                            'name'      => $translator->trans('form.lbl.rtl', array(), 'admin') != 'form.lbl.rtl' ? $translator->trans('form.lbl.rtl', array(), 'admin') : $translator->trans('lbl.rtl', array(), 'admin'),
                                            'selected'  => false,
                                            'value'     => 'rtl',
                                        ),
                                    ),
                                ),
                                array(
                                    'id'        => 'site',
                                    'label'     => $translator->trans('form.lbl.site', array(), 'admin') != 'form.lbl.site' ? $translator->trans('form.lbl.site', array(), 'admin') : $translator->trans('lbl.site', array(), 'admin'),
                                    'name'      => 'site',
                                    'size'      => 12,
                                    'settings'  => array('wrapInRow' => true, 'rowSize' => 6),
                                    'type'      => 'dropDown',
                                    'options'   => $siteOptions,
                                ),
                            ),
                        ),
                        array(
                            'size'          => 12,
                            'inputs'            => array(
                                array(
                                    'id'        => 'status',
                                    'label'     => $translator->trans('lbl.status', array(), 'admin'),
                                    'name'      => 'status',
                                    'size'      => 12,
                                    'settings'  => array('wrapInRow' => true, 'rowSize' => 12),
                                    'type'      => 'dropDown',
                                    'options'   => $statusOptions,
                                ),
                            ),
                        ),
                    ),
                ),
                'groupCode' => 'langDetail',
            ),
        );
        $widgetIcon = $this->url['domain'].'/themes/'.$current_page->getLayout()->getTheme()->getFolder().'/img/icons/light-sh/flag.png';
        $renderedLanguageForm = $coreRender->renderGenericFormWidget($widgetActions, $widgetContent, $core, $widgetTitle, $widgetIcon, $widgetSettings);
        unset($inputs, $widgetIcon, $widgetSettings, $widgetActions, $widgetContent, $widgetTitle);

        /**
         * 4. REQUIRED :: PREPARE TEMPLATE TAGS
         */
        $vars = array(
            'flash' => $flash,
            'page' => array(
                'blocks' => $blocks,
                'entity' => $current_page,
                'form'   => array(
                    'action'    => $this->url['base_l'].'/manage/language/process/new',
                    'csfr'      => $this->generateCSFR($session),
                    'method'    => 'post',
                ),
                'meta' => array(
                    'description' => $current_page->getLocalization($locale)->getMetaDescription(),
                    'keywords' => $current_page->getLocalization($locale)->getMetaKeywords(),
                    'title' => $current_page->getLocalization($locale)->getTitle(),
                ),
            ),
            'renderedLanguageForm' => $renderedLanguageForm,
            'renderedProjectLogo' => $renderedProjectLogo,
            'renderedSidebarNavigation' => $renderedSidebarNavigation,
            'renderedSidebarSeparator' => $renderedSidebarSeparator,
            'renderedTopNavigation' => $topNavigation,
            'site' => array(
                'entity' => $site,
                'name' => $site->getTitle(),
            ),
            'style' => array(
                'body' => array(
                    'classes' => array(),
                ),
            ),
            'xssCode' => $this->generateXssCode(),
        );

        $css = array('css/style.css', 'css/bootstrap-switch.css');
        $js = array(
            'js/libs/modernizr-2.6.2.min.js',
            'js/libs/jquery-1.10.2.js',
            'js/libs/json2.js',
            'js/libs/bootstrap.js',
            'js/plugins/validate/jquery.validate.1.11.1.js',
            'js/plugins/collapsible/collapsible.js',
            'js/plugins/switch/bootstrap-switch.min.js',
            'js/plugins/form2js/form2js.js',
            'js/plugins/form2js/jquery.toObject.js',
            'js/plugins/form2js/js2form.js'
        );

        /**
         * 5. REQUIRED :: MERGE PREPARED TAGS WITH DEFAULTS
         */
        $tags = $this->initDefaults($css, $js, $vars, $current_page->getLayout()->getTheme()->getFolder());

        /**
         * 6. REQUIRED :: RENDER PAGE
         *      note that if you do not want to immediately render the view to browser you need to use
         *      renderView() method instead of render() method.
         */
        return $this->render($current_page->getBundleName().':'.$current_page->getLayout()->getTheme()->getFolder().'/Pages:p_'.$pageCode.'.html.smarty', $tags);
    }
    /**
     * @name            processAction()
     *                  DOMAIN/{_locale}/manage/language/process/{action}/{id}
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           string          $action     new, edit
     * @param           integer         $id         id of the edited item
     *
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function processAction($action = 'new', $id = -1) {
        /**
         * 1. Get global services and prepare URLs
         */
        $session = $this->get('session');
        $request = $this->get('request');
        $translator = $this->get('translator');
        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');

        $this->setURLs();

        $locale = $session->get('_locale');

        /**
         * 2. Validate Access Rights
         *
         * This controller is managed and only available to non-loggedin users.
         */
        $access_map = array(
            'unmanaged' => false,
            'guest' => false,
            'authenticated' => true,
            'members' => array(),
            'groups' => array('founder', 'support', 'admin'),
            'status' => array('a')
        );
        if (!$av->has_access(null, $access_map)) {
            $sm->logAction('page.visit.fail.insufficient.rights', 1, array('route' => '/manage/language/process/'.$action));
            /** If already logged-in redirect back to Manage/Dashboard */
            return new RedirectResponse($this->url['base_l'] . '/manage/account/login');
        }

        $form = $request->get('mainForm');
        $jsonData = json_decode($form['data']['json']);
        $data = $jsonData[0];
        if($data->csfr != $session->get('_csfr')){
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'danger');
            /** $response[$code] must have a corresponding translation */
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.security.csfr', array(), 'admin'));
            /** csfr error */
            if($action == 'new'){
                return new RedirectResponse($this->url['base_l'] . '/manage/language/new');
            }
            else{
                return new RedirectResponse($this->url['base_l'] . '/manage/language/edit/'.$id);
            }
        }

        $mlsModel = $this->get('multilanguagesupport.model');

        foreach($data->langDetail as $lang){
            if($action == 'edit' && property_exists($data, 'entry_id')){
                $lang->id = $data->entry_id;
            }

            if(!property_exists($lang, 'url_key')){
                $lang->url_key = $this->generateUrlKey($lang->name);
            }
        }
        switch($action){
            case 'new':
                $response = $mlsModel->insertLanguages($data->langDetail);
                break;
            case 'edit':
                $response = $mlsModel->updateLanguages($data->langDetail);
                break;
        }

        if($response['error']){
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'danger');
            /** $response[$code] must have a corresponding translation */
            $session->getFlashBag()->add('msg.content', $translator->trans('form.err.db.insert', array(), 'admin'));
            /** csfr error */
            switch($action){
                case 'edit':
                    $return = '/edit/'.$id;
                    break;
                case 'new':
                    $return = '/new';
                    break;
            }
            return new RedirectResponse($this->url['base_l'] . '/manage/language'.$return);
        }
        $session->getFlashBag()->add('msg.status', true);
        $session->getFlashBag()->add('msg.type', 'success');
        /** $response[$code] must have a corresponding translation */
        if($action == 'new'){
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.success.add', array(), 'admin'));
            return new RedirectResponse($this->url['base_l'] . '/manage/language/new');
        }
        else if($action == 'edit'){
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.success.update', array(), 'admin'));
            return new RedirectResponse($this->url['base_l'] . '/manage/language/list');
        }
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.0                      Can Berkol
 * 28.01.2014
 * **************************************
 * A listAction
 * A newAction
 * A processAction
 *
 */