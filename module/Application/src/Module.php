<?php
/**
 *   ocs-webserver
 *
 *   Copyright 2016 by pling GmbH.
 *
 *     This file is part of ocs-webserver.
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Application;

use Application\Model\Service\AclService;
use Application\Model\Service\AuthManager;
use Application\Model\Service\CurrentStoreReader;
use Application\Model\Service\Interfaces\CurrentStoreReaderInterface;
use Application\Model\Service\UrlEncrypt;
use Exception;
use Laminas\Config\Config;
use Laminas\Db\TableGateway\Feature\GlobalAdapterFeature;
use Laminas\Log\Logger;
use Laminas\Mvc\Application;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Session\SessionManager;
use Laminas\Uri\UriFactory;
use Laminas\View\Helper\Placeholder\Container\AbstractContainer;
use Laminas\View\Helper\ServerUrl;

class Module
{
    const VERSION = '2.0';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * This method is called once the MVC bootstrapping is complete and allows
     * to register event listeners.
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        $adapter = $sm->get('Laminas\Db\Adapter\Adapter');
        GlobalAdapterFeature::setStaticAdapter($adapter);

        $sessionManager = $sm->get(SessionManager::class);
        $this->forgetInvalidSession($sessionManager, $sm->get('Ocs_Log'));

        $this->initGlobals($sm);

        // Get event manager.
        $eventManager = $e->getApplication()->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();
        // Register the event listener method with abstract controller class for ACL check.
        $sharedEventManager->attach(
            AbstractActionController::class, MvcEvent::EVENT_DISPATCH, [
            $this,
            'checkAcl',
        ], 100
        );
        $sharedEventManager->attach(Application::class, MvcEvent::EVENT_ROUTE, array($this, 'initStore'), 100);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), 100);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'onDispatchError'), 100);
    }

    protected function forgetInvalidSession(SessionManager $sessionManager, Logger $logger)
    {
        try {
//            $remoteAddr = new \Laminas\Session\Validator\RemoteAddr();
//            //$remoteAddr->setTrustedProxies([]);
//            $remoteAddr->setProxyHeader('HTTP_X_FORWARDED_FOR');
//            $remoteAddr->setUseProxy(true);
//
//
//            $current_chain = $sessionManager->getValidatorChain(\Laminas\Session\Validator\RemoteAddr::class);
//
//            $current_chain->attach(
//                'session.validate',
//                [ new \Laminas\Session\Validator\HttpUserAgent(), 'isValid' ]
//            );
//
//            $current_chain->attach(
//                'session.validate',
//                [ $remoteAddr, 'isValid' ]
//            );
//
//            $sessionManager->start();
//
//            Container::setDefaultManager($sessionManager);
            $sessionManager->start();

            return;
        } catch (Exception $e) {
            $logger->err(
                __METHOD__ . ' - ' . get_class($e) . ' -> ' . $e->getMessage() . ' - ' . $e->getFile() . '(' . $e->getLine() . ') ' . json_encode(
                    $sessionManager->getStorage()->getMetadata()
                ) . ' ' . json_encode($_SERVER['HTTP_USER_AGENT'])
            );
        }
        /**
         * Session validation failed: toast it and carry on.
         */
        // @codeCoverageIgnoreStart
        session_unset();
        // @codeCoverageIgnoreEnd
    }

    protected function initGlobals(ServiceLocatorInterface $sm)
    {
        // @codeCoverageIgnoreStart
        UriFactory::registerScheme('chrome-extension', 'Zend\Uri\Uri');
        UriFactory::registerScheme('chrome-error', 'Zend\Uri\Uri');

        $config = $sm->get('config');
        $app_version = $config['ocs_config']['version'];
        defined('APPLICATION_VERSION') || define('APPLICATION_VERSION', $app_version);
        defined('APPLICATION_DATA') || define('APPLICATION_DATA', realpath(__DIR__ . '/../../../data/'));

        global $ocs_config;
        $ocs_config = new Config($config['ocs_config']);

        global $headMetaSet;
        $headMetaSet = false;

        defined('SERVER_BASE_PATH') || define('SERVER_BASE_PATH', '');

        defined('IMAGES_UPLOAD_PATH') || define('IMAGES_UPLOAD_PATH', $config['ocs_config']['settings']['server']['images']['upload']['path']);
        defined('IMAGES_MEDIA_SERVER') || define('IMAGES_MEDIA_SERVER', $config['ocs_config']['settings']['server']['images']['media']['server']);
        defined('VIDEOS_UPLOAD_PATH') || define('VIDEOS_UPLOAD_PATH', $config['ocs_config']['settings']['server']['videos']['upload']['path']);
        defined('VIDEOS_MEDIA_SERVER') || define('VIDEOS_MEDIA_SERVER', $config['ocs_config']['settings']['server']['videos']['media']['server']);
        defined('COMICS_MEDIA_SERVER') || define('COMICS_MEDIA_SERVER', $config['ocs_config']['settings']['server']['comics']['media']['server']);

        // fileserver
        defined('PPLOAD_API_URI') || define('PPLOAD_API_URI', $config['ocs_config']['settings']['server']['files']['api']['uri']);
        defined('PPLOAD_CLIENT_ID') || define('PPLOAD_CLIENT_ID', $config['ocs_config']['settings']['server']['files']['api']['client_id']);
        defined('PPLOAD_SECRET') || define('PPLOAD_SECRET', $config['ocs_config']['settings']['server']['files']['api']['client_secret']);
        defined('PPLOAD_HOST') || define('PPLOAD_HOST', $config['ocs_config']['settings']['server']['files']['host']);
        defined('PPLOAD_DOWNLOAD_SECRET') || define('PPLOAD_DOWNLOAD_SECRET', $config['ocs_config']['settings']['server']['files']['download_secret']);

        global $ocs_cache;
        $ocs_cache = $sm->get('Application\Model\Factory\CacheFactory');

        global $ocs_session;
        $ocs_session = $sm->get('Ocs_Global');

        global $ocs_log;
        $ocs_log = $sm->get('Ocs_Log');

        global $ocs_user;
        /** @var AuthManager $auth_manager */
        $auth_manager = $sm->get('Application\Model\Service\AuthManager');
        $ocs_user = $auth_manager->getCurrentUser();

        // @codeCoverageIgnoreEnd
    }

    /**
     * @param MvcEvent $e The MvcEvent instance
     *
     * @return void
     */
    public function setLayoutTitle($e)
    {
        $siteName = 'pling.com';

        // Getting the view helper manager from the application service manager
        $viewHelperManager = $e->getApplication()->getServiceManager()->get('ViewHelperManager');

        // Getting the headTitle helper from the view helper manager
        $headTitleHelper = $viewHelperManager->get('headTitle');

        // Setting a separator string for segments
        $headTitleHelper->setSeparator(' - ');
        $headTitleHelper->setDefaultAttachOrder(AbstractContainer::PREPEND);

        // Setting the action, controller, module and site name as title segments
        $headTitleHelper->append($siteName);
    }

    /**
     * @param MvcEvent $event
     *
     * @throws Exception
     */
    public function checkAcl(MvcEvent $event)
    {
        // Get controller and action to which the HTTP request was dispatched.
        $controller = $event->getTarget();
        $controllerName = $event->getRouteMatch()->getParam('controller', null);
        $actionName = $event->getRouteMatch()->getParam('action', null);

        $matches = $event->getRouteMatch();

        $sm = $event->getApplication()->getServiceManager();
        /** @var AclService $acl_service */
        $acl_service = $sm->get(AclService::class);
        $acl_service->setRequestedParams(array_merge($matches->getParams(), $_GET, $_POST));

        /** @var AuthManager $auth_manager */
        $auth_manager = $sm->get(AuthManager::class);
        $user = $auth_manager->getCurrentUser();

        $result = $acl_service->isGranted($user, $controllerName, $actionName);

        if ($result == AclService::ACCESS_GRANTED) {
            return;
        }

        if ($result == AclService::AUTH_REQUIRED) {
            // Remember the URL of the page the user tried to access. We will
            // redirect the user to that URL after successful login.
            $uri = $event->getApplication()->getRequest()->getUri();
            // Make the URL relative (remove scheme, user info, host name and port)
            // to avoid redirecting to other domain by a malicious user.
            $uri->setScheme(null)->setHost(null)->setPort(null)->setUserInfo(null);
            $redirectUrl = $uri->toString();
            $filter = new UrlEncrypt();
            $redirect = $filter->encryptForUrl($redirectUrl);

            // Redirect the user to the "Login" page.
            return $controller->redirect()->toRoute('not-authorized', [], ['query' => ['redirect' => $redirect]]);
//            return $event->getTarget()->redirect()->toRoute('application_login', [], ['query' => ['redirect' => $redirect]]);
        }

        if ($result == AclService::ACCESS_DENIED) {
            // Redirect the user to the "Not Authorized" page.
            return $controller->redirect()->toRoute('not-authorized');
        }
    }

    // Event listener method.
    public function initStore(MvcEvent $event)
    {
//        if (php_sapi_name() == "cli") {
//            // Do not execute HTTPS redirect in console mode.
//            return;
//        }
//
//        // Get request URI
//        $uri = $event->getRequest()->getUri();
//        $scheme = $uri->getScheme();
//        // If scheme is not HTTPS, redirect to the same URI, but with
//        // HTTPS scheme.
//        if ($scheme != 'https'){
//            $uri->setScheme('https');
//            $response=$event->getResponse();
//            $response->getHeaders()->addHeaderLine('Location', $uri);
//            $response->setStatusCode(301);
//            $response->sendHeaders();
//            return $response;
//        }
        // @codeCoverageIgnoreStart

        $sm = $event->getApplication()->getServiceManager();

        global $ocs_store;
        /** @var CurrentStoreReaderInterface $current_store_service */
        $current_store_service = $sm->get(CurrentStoreReader::class);
        $ocs_store = $current_store_service->getCurrentStore();

        global $ocs_store_category_list;
        $ocs_store_category_list = $ocs_store->categories;

        global $ocs_config_store_tags;
        if ($ocs_store->tags) {
            $ocs_config_store_tags = array_column($ocs_store->tags, 'tag_id');
        } else {
            $ocs_config_store_tags = null;
        }

        global $ocs_config_store_taggroups;
        if ($ocs_store->tag_groups) {
            $ocs_config_store_taggroups = $ocs_store->tag_groups;
        } else {
            $ocs_config_store_taggroups = null;
        }

        // @codeCoverageIgnoreEnd
    }

    // Event listener method.
    public function onDispatchError(MvcEvent $event)
    {
        $sm = $event->getApplication()->getServiceManager();
        $logger = $sm->get('Ocs_Log');
        if ($event->isError()) {
            $eventParams = $event->getParams();
            if (isset($eventParams['exception'])) {
                /** @var Exception $exception */
                $exception = $eventParams['exception'];
                $logger->err(__METHOD__ . ' - ' . get_class($exception) . ' -> ' . $exception->getMessage() . ' - ' . $exception->getFile() . '(' . $exception->getLine() . ') ' . json_encode($_SERVER));
                $logger->err(__METHOD__ . ' - ' . $exception->getTraceAsString());
            }
            $viewHelperManager = $sm->get('ViewHelperManager');
            /** @var ServerUrl $serverUrl */
            $serverUrl = $viewHelperManager->get('serverUrl');
            $host = $serverUrl->setUseProxy(true)->getHost();
            $scheme = $serverUrl->setUseProxy(true)->getScheme();
            $logger->err(__METHOD__ . ' - ' . $event->getError() . ' -> ' . $scheme . '://' . $host . $_SERVER['REQUEST_URI']);
        }
        $vm = $event->getViewModel();
        $vm->setTemplate('layout/flat-ui');
    }

}
 