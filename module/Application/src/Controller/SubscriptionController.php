<?php /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUndefinedFieldInspection */

/**
 *  ocs-webserver
 *
 *  Copyright 2016 by pling GmbH.
 *
 *    This file is part of ocs-webserver.
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * */

namespace Application\Controller;

use Application\Model\Repository\ProjectRepository;
use Application\Model\Repository\SectionSupportRepository;
use Application\Model\Repository\SupportRepository;
use Application\Model\Service\HtmlPurifyService;
use Application\Model\Service\InfoService;
use Application\Model\Service\SectionService;
use Application\View\Helper\CalcDonation;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Http\Request;

/**
 * Class SubscriptionController
 *
 * @package Application\Controller
 */
class SubscriptionController extends DomainSwitch
{

    public static $SUPPORT_OPTIONS = array(
        // 'Option1' => array(
        //     "name"             => "Option1",
        //     "amount"           => 0.99,
        //     "checked"          => "checked",
        //     "period"           => "monthly",
        //     "period_short"     => "M",
        //     "period_frequency" => "1",
        // ),
        'Option1' => array(
            "name"             => "Option1",
            "amount"           => 1,
            "checked"          => "checked",
            "period"           => "monthly",
            "period_short"     => "M",
            "period_frequency" => "1",
        ),
        'Option2' => array(
            "name"             => "Option2",
            "amount"           => 2,
            "checked"          => "",
            "period"           => "monthly",
            "period_short"     => "M",
            "period_frequency" => "1",
        ),
        'Option3' => array(
            "name"             => "Option3",
            "amount"           => 5,
            "checked"          => "",
            "period"           => "monthly",
            "period_short"     => "M",
            "period_frequency" => "1",
        ),
        'Option4' => array(
            "name"             => "Option4",
            "amount"           => 10,
            "checked"          => "",
            "islast"           => true,
            "period"           => "monthly",
            "period_short"     => "M",
            "period_frequency" => "1",
        ),
        'Option5' => array(
            "name"             => "Option5",
            "amount"           => 1,
            "checked"          => "",
            "period"           => "yearly",
            "period_short"     => "Y",
            "period_frequency" => "1",
        ),
        'Option6' => array(
            "name"             => "Option6",
            "amount"           => 2,
            "checked"          => "",
            "period"           => "yearly",
            "period_short"     => "Y",
            "period_frequency" => "1",
        ),
        'Option7' => array(
            "name"             => "Option7",
            "amount"           => 0,
            "checked"          => "",
            "period"           => "yearly",
            "period_short"     => "Y",
            "period_frequency" => "1",
        ),
        'Option8' => array(
            "name"             => "Option8",
            "amount"           => 5,
            "checked"          => "",
            "period"           => "yearly",
            "period_short"     => "Y",
            "period_frequency" => "1",
        ),
        'Option9' => array(
            "name"             => "Option9",
            "amount"           => 10,
            "checked"          => "",
            "period"           => "yearly",
            "period_short"     => "Y",
            "period_frequency" => "1",
        ),
    );
    protected $configArray;
    protected $_request = null;
    protected $_authMember;
    protected $isAdmin = false;
    protected $projectRepository;
    protected $projectCategoryRepository;
    protected $infoService;
    protected $sectionService;
    protected $supportRepository;
    protected $sectionSupportRepository;

    public function __construct(
        AdapterInterface $db,
        array $config,
        Request $request,
        InfoService $infoService,
        ProjectRepository $projectRepository,
        SectionService $sectionService,
        SupportRepository $supportRepository,
        SectionSupportRepository $sectionSupportRepository
    ) {
        parent::__construct($db, $config, $request);
        parent::init();
        $this->projectRepository = $projectRepository;
        $this->infoService = $infoService;
        $this->sectionService = $sectionService;
        $this->supportRepository = $supportRepository;
        $this->sectionSupportRepository = $sectionSupportRepository;

        $this->_authMember = $this->view->getVariable('ocs_user');
        $this->configArray = $config;

        $this->view->setVariable('payment_options', $this::$SUPPORT_OPTIONS);
        $this->view->setVariable('noheader', true);
    }

    public function supportpredefindedAction()
    {
        $this->setLayout();
        $this->layout()->noheader = true;

        $this->view->setVariable('authMember', $this->_authMember);
        $this->view->setVariable('headTitle', 'Become a supporter - ' . $this->getHeadTitle());
        $this->view->setVariable('urlPay', '/support/paypredefined');

        $amount_predefined = (float)$this->getParam('amount_predefined', null);
        $section_id = (float)$this->getParam('section_id', null);
        $project_id = (float)$this->getParam('project_id', null);
        $support_amount = (float)$this->getParam('support_amount', null);

        $sectionsTable = $this->sectionService;
        $section = $sectionsTable->fetchSection($section_id);

        $referer = $this->getParam('referer', null);
        if (null == $referer && !empty($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }

        $this->view->setVariable('amount_predefined', $amount_predefined);
        $this->view->setVariable('support_amount', $support_amount);
        $this->view->setVariable('section_id', $section_id);
        $this->view->setVariable('project_id', $project_id);
        $this->view->setVariable('referer', urlencode($referer));
        $this->view->setVariable('section', $section);

        return $this->view;
    }

    public function supportAction()
    {
        $this->setLayout();
        $this->layout()->noheader = true;

        $this->view->setVariable('authMember', $this->_authMember);
        $this->view->setVariable('headTitle', 'Become a supporter - ' . $this->getHeadTitle());
        $this->view->setVariable('urlPay', '/support/pay');
        $creator_id = $this->getParam('creator_id', null);
        $this->view->setVariable('creator_id', $creator_id);

        $sectionsTable = $this->sectionService;
        $sections = $sectionsTable->fetchAllSections();
        $this->view->setVariable('sections', $sections);

        return $this->view;
    }

    public function showAction()
    {
        $this->setLayout();
        $this->layout()->noheader = true;

        $this->view->setVariable('authMember', $this->_authMember);
        $this->view->setVariable('headTitle', 'Become a supporter - ' . $this->getHeadTitle());
        $this->indexAction();
    }

    public function indexAction()
    {
        $this->setLayout();
        $this->layout()->noheader = true;

        $this->view->setVariable('authMember', $this->_authMember);
        $this->view->setVariable('headTitle', 'Become a supporter - ' . $this->getHeadTitle());
        $this->view->setVariable('urlPay', '/support/pay');

        $modelInfo = $this->infoService;
        $countActiveMembers = $modelInfo->countTotalActiveMembers();
        $this->view->setVariable('countActiveMembers', $countActiveMembers);

        $sectionsTable = $this->sectionService;
        $sections = $sectionsTable->fetchAllSections();
        $this->view->setVariable('sections', $sections);

        return $this->view;
    }

    public function payAction()
    {
        //$this->setLayout();
        $this->view->setTerminal(true);
        $this->view->setVariable('headTitle', 'Become a supporter - ' . $this->getHeadTitle());

        $sectionsTable = $this->sectionService;
        $sections = $sectionsTable->fetchAllSections();

        $amount = 0;

        $paymentFrequenz = $this->getParam('paymentFrequenz', 'Y');
        $creator_id = $this->getParam('creator_id', null);
        $this->view->setVariable('creator_id', $creator_id);

        //get parameter for every section
        $supportArray = array();
        foreach ($sections as $section) {

            $paymentOption = $this->getParam('amount_predefined-' . $section['section_id'], null);
            $amount_predefined = (float)$this->getParam('amount_predefined-' . $section['section_id'], null);
            $amount_handish = (float)$this->getParam('amount_handish-' . $section['section_id'], null);
            $calModel = new CalcDonation();

            if (null != $paymentOption) {
                $isHandish = false;
                $data = array();
                if ($paymentOption != 'Option7') {

                    $amount += 1;
                    $data['section_id'] = $section['section_id'];
                    $data['amount'] = 1;
                    $data['tier'] = 1;
                    $data['period'] = $paymentFrequenz;
                    $data['period_frequency'] = 1;

                    // $amount += 0.99;
                    // $data['section_id'] = $section['section_id'];
                    // $data['amount'] = 0.99;
                    // $data['tier'] = 0.99;
                    // $data['period'] = $paymentFrequenz;
                    // $data['period_frequency'] = 1;
                } else {
                    if (null != $amount_handish) {
                        $isHandish = true;
                        $amount += $amount_handish;

                        $data['section_id'] = $section['section_id'];
                        $data['amount'] = $amount_handish;
                        $data['tier'] = $amount_handish;
                        $data['period'] = $paymentFrequenz;
                        $data['period_frequency'] = 1;
                    }
                }
                $supportArray[] = $data;
            }
        }

        $amountTier = $amount;

        if ($paymentFrequenz == 'Y') {
            $amount = $calModel->calcDonation($amount * 12);
        } else {
            $amount = $calModel->calcDonation($amount);
        }

        $comment = HtmlPurifyService::purify($this->getParam('comment'));
        $paymentProvider = mb_strtolower(html_entity_decode(strip_tags($this->getParam('provider'), null), ENT_QUOTES, 'utf-8'), 'utf-8');
        $httpHost = $_SERVER["HTTP_HOST"] . '';
        $config = $this->config;

        $form_url = $config->third_party->paypal->form->endpoint . '/cgi-bin/webscr';
        $ipn_endpoint = 'http://' . $httpHost . '/gateway/paypal';
        $return_url_success = 'http://' . $httpHost . '/support/paymentok';
        $return_url_cancel = 'http://' . $httpHost . '/support/paymentcancel';
        $merchantid = $config->third_party->paypal->merchantid;

        $this->view->setVariable('form_endpoint', $form_url);
        $this->view->setVariable('form_ipn_endpoint', $ipn_endpoint);
        $this->view->setVariable('form_return_url_ok', $return_url_success);
        $this->view->setVariable('form_return_url_cancel', $return_url_cancel);
        $this->view->setVariable('form_merchant', $merchantid);
        $this->view->setVariable('member_id', $this->_authMember->member_id);
        $this->view->setVariable('transaction_id', $this->_authMember->member_id . '_' . time());

        $this->view->setVariable('amount', $amount);
        $this->view->setVariable('amountTier', $amountTier);
        $this->view->setVariable('paymentFrequenz', $paymentFrequenz);
        $this->view->setVariable('payment_option', $paymentOption);


        //Add pling
        $modelSupport = $this->supportRepository;
        $supportId = $modelSupport->createNewSupportSubscriptionSignup(
            $this->view->getVariable('transaction_id'), $this->_authMember->member_id, $amount, $amountTier, $paymentFrequenz, 1
        );

        //Save Section-Support
        foreach ($supportArray as $support) {
            $modelSectionSupport = $this->sectionSupportRepository;
            $sectionSupportId = $modelSectionSupport->createNewSectionSupport(
                $supportId, $support['section_id'], $support['amount'], $support['tier'], $support['period'], $support['period_frequency'], null, $creator_id, null, null
            );
        }

        return $this->view;
    }

    public function paypredefinedAction()
    {
        //$this->setLayout();
        $this->view->setTerminal(true);

        $amount = 0;

        //get parameter
        $paymentFrequenz = $this->getParam('paymentFrequenz', 'Y');
        $section_id = $this->getParam('section_id', null);

        $project_id = $this->getParam('project_id', null);
        $referer = $this->getParam('referer', null);

        $creator_id = $this->getParam('creator_id', null);

        $project = null;

        $project_category_id = null;

        if (null != $project_id) {
            $projectTable = $this->projectRepository;
            $project = $projectTable->fetchProductInfo($project_id);

            if ($project) {
                $creator_id = $project['project_member_id'];
                $project_category_id = $project['project_category_id'];
            }
        }

        $amount_predefined = (float)$this->getParam('amount_predefined', null);
        $amount_handish = (float)$this->getParam('amount_handish', null);

        $isHandish = false;

        $amount = 0;
        if (null != ($this->getParam('amount_predefined') && $amount_predefined > 0)) {
            $amount = $amount_predefined;
        } else {
            $isHandish = true;
            $amount = $amount_handish;
        }

        $paymentProvider = mb_strtolower(html_entity_decode(strip_tags($this->getParam('provider'), null), ENT_QUOTES, 'utf-8'), 'utf-8');
        $httpHost = $_SERVER["HTTP_HOST"] . '';
        $config = $this->config;

        $form_url = $config->third_party->paypal->form->endpoint . '/cgi-bin/webscr';
        $ipn_endpoint = 'http://' . $httpHost . '/gateway/paypal';
        $return_url_success = 'http://' . $httpHost . '/support/paymentok';
        $return_url_cancel = 'http://' . $httpHost . '/support/paymentcancel';
        $merchantid = $config->third_party->paypal->merchantid;

        $this->view->setVariable('form_endpoint', $form_url);
        $this->view->setVariable('form_ipn_endpoint', $ipn_endpoint);
        $this->view->setVariable('form_return_url_ok', $return_url_success);
        $this->view->setVariable('form_return_url_cancel', $return_url_cancel);
        $this->view->setVariable('form_merchant', $merchantid);
        $this->view->setVariable('member_id', $this->_authMember->member_id);
        $this->view->setVariable('transaction_id', $this->_authMember->member_id . '_' . time());
        $this->view->setVariable('amount', $amount);
        $this->view->setVariable('paymentFrequenz', $paymentFrequenz);

        //Add pling
        $modelSupport = $this->supportRepository;
        $calModel = new CalcDonation();

        if ($paymentFrequenz == 'Y') {
            $v = $calModel->calcDonation($amount * 12);
        } else {
            $v = $calModel->calcDonation($amount);
        }
        $this->view->setVariable('amountPay', $v);

        $supportId = $modelSupport->createNewSupportSubscriptionSignup(
            $this->view->getVariable('transaction_id'), $this->_authMember->member_id, $v, $amount, $paymentFrequenz, 1
        );


        $modelSectionSupport = $this->sectionSupportRepository;
        $sectionSupportId = $modelSectionSupport->createNewSectionSupport(
            $supportId, $section_id, $v, $amount, $paymentFrequenz, 1, $project_id, $creator_id, $project_category_id, urldecode($referer)
        );

        return $this->view;
    }

    public function paymentokAction()
    {
        $this->setLayout();
        //$this->_helper->layout()->disableLayout();
        $this->view->setVariable('paymentStatus', 'success');
        $this->view->setVariable('paymentMessage', 'Payment successful.');
        $this->view->setVariable('headTitle', 'Thank you for your support - ' . $this->getHeadTitle());

        return $this->view;
    }

    public function paymentcancelAction()
    {
        $this->setLayout();
        $this->view->setVariable('paymentStatus', 'danger');
        $this->view->setVariable('paymentMessage', 'Payment cancelled.');
        $this->view->setVariable('headTitle', 'Become a supporter - ' . $this->getHeadTitle());

        return $this->view;
    }

    /**
     * @param $errors
     *
     * @return array
     */
    protected function getErrorMessages($errors)
    {
        $messages = array();
        foreach ($errors as $element => $row) {
            if (!empty($row) && $element != 'submit') {
                foreach ($row as $validator => $message) {
                    $messages[$element][] = $message;
                }
            }
        }

        return $messages;
    }

    protected function _initResponseHeader()
    {
        $duration = 1800; // in seconds
        $expires = gmdate("D, d M Y H:i:s", time() + $duration) . " GMT";

        $this->getResponse()
             ->setMetadata('X-FRAME-OPTIONS', 'ALLOWALL', true)//            ->setHeader('Last-Modified', $modifiedTime, true)
             ->setMetadata('Expires', $expires, true)->setMetadata('Pragma', 'no-cache', true)
             ->setMetadata('Cache-Control', 'private, no-cache, must-revalidate', true);
    }

}
