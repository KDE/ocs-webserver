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

namespace Application\Controller;

use Application\Form\PasswordChangeForm;
use Application\Model\Repository\MemberRepository;
use Application\Model\Service\EmailBuilderFileTemplate;
use Application\Model\Service\MemberService;
use Application\Model\Service\Ocs\Ldap;
use Application\Model\Service\Ocs\OAuth;
use Exception;
use JobQueue\Jobs\EmailJob;
use JobQueue\Jobs\JobBuilder;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Http\Request;
use Laminas\Validator\EmailAddress;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Library\Filter\Url\Decrypt;
use Library\Filter\Url\Encrypt;
use Library\Tools\PasswordEncrypt;
use RuntimeException;

/**
 * Class PasswordController
 *
 * @package Application\Controller
 */
class PasswordController extends BaseController
{

    const C = 10800;
    private $from_alias = "opendesktop.org";
    private $from_mail = "contact@opendesktop.org";
    private $options = array(
        // Encryption type - Openssl or Mcrypt
        'adapter'   => 'mcrypt',
        // Initialization vector
        'vector'    => '236587hgtyujkirtfgty5678',
        // Encryption algorithm
        'algorithm' => 'rijndael-192',
        // Encryption key
        'key'       => 'KFJGKDK$$##^FFS345678FG2',
    );
    /**
     * @var StorageInterface
     */
    private $cache;
    /**
     * @var MemberService
     */
    private $memberService;
    /**
     * @var PhpRenderer
     */
    private $renderer;

    public function __construct(StorageInterface $cache, MemberService $memberService, PhpRenderer $renderer)
    {
        parent::__construct();
        $this->cache = $cache;
        $this->memberService = $memberService;
        $this->renderer = $renderer;
    }

    public function requestAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');

        if ($this->getRequest()->isGet()) {
            return new ViewModel();
        }

        if ($this->getRequest()->isPost()) {
            $email = $this->params()->fromPost('mail');
            if (empty($email)) {
                $this->flashMessenger()->addInfoMessage('Field mail should not empty.');

                return new ViewModel();
            }

            $validate = new EmailAddress();
            if (false == $validate->isValid($email)) {
                $this->flashMessenger()->addInfoMessage('Please type in a valid email address.');

                return new ViewModel();
            }

            $modelMember = $this->memberService;
            $member = $modelMember->findActiveMemberByMail($email);

            if (empty($member->member_id)) {

                $member = $modelMember->findActiveMemberByMail($email . '_double');

                if (empty($member->member_id)) {
                    return new ViewModel();
                }
            }

            $url = $this->generateUrl($member);

            $this->sendMail($email, $url, 'Reset your password');

            $this->flashMessenger()->addInfoMessage(
                    'We have sent you an email. Please check your mailbox and follow the instructions to change your password.'
                );

            return $this->redirect()->toRoute('application_login');
        }
    }

    private function generateUrl($member)
    {
        $encrypt = new Encrypt;

        $duration = self::C; // in seconds

        $payload = array(
            'member_id' => $member->member_id,
            'expire'    => time() + $duration,
        );

        /** @var Request $request */
        $request = $this->getRequest();

        $secret = $this->base64url_encode($encrypt->filter(json_encode($payload)));

        $url = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . '/password/change?' . $secret;

        $this->ocsLog->debug(__METHOD__ . ' - generated url: ' . $url);

        $this->cache->setItem(sha1($secret), $secret);

        return $url;
    }

    protected function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @param $email
     * @param $url
     * @param $subject
     *
     */
    private function sendMail($email, $url, $subject)
    {
        $renderer = $this->renderer;
        $renderer->layout()->setTemplate('/emails/layout.phtml');
        $view_model = new ViewModel();
        $view_model->setTemplate('emails/url_forgot_password.phtml');

        $view_model->setVariable('mail', $email);
        $view_model->setVariable('url', $url);

        $body_text = $renderer->render($view_model);

        try {

            /** @var EmailBuilderFileTemplate $confirmMail */
            $confirmMail = $this->getEvent()->getApplication()->getServiceManager()->get(
                EmailBuilderFileTemplate::class
            );
            $mail_config = $GLOBALS['ocs_config']['settings']['mail'];
            $mail = $confirmMail->withTemplate('emails/url_forgot_password.phtml')
                                ->setTemplateVar('mail', $email)
                                ->setTemplateVar('url', $url)
                                ->setReceiverMail($email)
                                ->setFromMail($this->from_mail)
                                ->setFromAlias($this->from_alias)
                                ->setSubject($subject)
                                ->build();

            JobBuilder::getJobBuilder()->withJobClass(EmailJob::class)->withParam('mail', serialize($mail))->withParam(
                    'withFileTransport', $mail_config['transport']['withFileTransport']
                )->withParam('withSmtpTransport', $mail_config['transport']['withSmtpTransport'])->withParam(
                    'config', serialize(
                    $mail_config
                )
                )->build();

        } catch (Exception $e) {
            $this->ocsLog->err(__METHOD__ . " - " . $e->getMessage() . PHP_EOL);
        }
    }

    public function changeAction()
    {
        $this->layout()->setTemplate('layout/flat-ui');

        $debugMsg = '' . __METHOD__ . PHP_EOL;
//        $uri_part = $this->getRequest()->getUri()->getQueryAsArray();
//        $debugMsg .= ' - $uri_part = ' . print_r($uri_part, true) . PHP_EOL;
//        $secret = preg_replace('/[^-a-zA-Z0-9_=\/]/', '', array_pop($uri_part));
        $secret = $this->getRequest()->getUri()->getQuery();
        $debugMsg .= ' - $secret = ' . print_r($secret, true) . PHP_EOL;

        $decrypt = new Decrypt();
        $step1 = $this->base64url_decode($secret);
        $debugMsg .= ' - $step1 = ' . print_r($step1, true) . PHP_EOL;
        $step2 = $decrypt->filter($step1);
        $debugMsg .= ' - $step2 = ' . print_r($step2, true) . PHP_EOL;
        $payload = json_decode(trim($step2), true);
        $debugMsg .= ' - $payload = ' . print_r($payload, true) . PHP_EOL;

        if (false == $this->cache->getItem(sha1($secret))) {
            $debugMsg .= '- unknown request url' . PHP_EOL;
            $this->ocsLog->debug($debugMsg);
            throw new RuntimeException('Unknown request url for password change');
        }

        if (empty($payload) or (false == is_array($payload))) {
            $debugMsg .= '- wrong request url' . PHP_EOL;
            $this->ocsLog->debug($debugMsg);
            throw new RuntimeException('Wrong request url for password change');
        }

        if (time() > $payload['expire']) {
            $debugMsg .= '- password change request expired' . PHP_EOL;
            $this->ocsLog->debug($debugMsg);
            $this->flashMessenger()->addInfoMessage('Your password change request is expired.');

            return $this->forward()->dispatch(AuthController::class, array('action' => 'login'));
        }

        $form = new PasswordChangeForm('reset');

        if ($this->getRequest()->isGet()) {
            $debugMsg .= '- show password change form' . PHP_EOL;
            $this->ocsLog->debug($debugMsg);

            return new ViewModel(
                array(
                    'form'   => $form,
                    'action' => '/password/change?' . $secret,
                )
            );
        }

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            // Fill in the form with POST data
            $data = $this->params()->fromPost();
            $form->setData($data);

            // Validate form
            if ($form->isValid()) {
                // Get filtered and validated data
                $data = $form->getData();

                $member_data = $this->memberService->fetchMember($payload['member_id']);
                $new_password = array();
                if ($member_data->password_type == MemberRepository::PASSWORD_TYPE_HIVE) {
                    //Save old data
                    $new_password['password_old'] = $member_data->password;
                    $new_password['password_type_old'] = MemberRepository::PASSWORD_TYPE_HIVE;

                    //Change type and password
                    $new_password['password_type'] = MemberRepository::PASSWORD_TYPE_OCS;
                }
                $new_password['password'] = PasswordEncrypt::get(
                    $data['new_password'], PasswordEncrypt::PASSWORD_TYPE_OCS
                );
                $new_password['member_id'] = $member_data->member_id;

                $this->memberService->getMemberRepository()->insertOrUpdate($new_password);

                $this->cache->removeItem(sha1($secret));

                //Update Auth-Services
                $current_member_data = $this->memberService->fetchMember($payload['member_id']);
                try {
                    /** @var OAuth $oauth */
                    $oauth = $this->getEvent()->getApplication()->getServiceManager()->get(OAuth::class);
                    $oauth->updatePasswordForUser($payload['member_id']);
                    $this->ocsLog->info(__METHOD__ . ' - oauth : ' . var_export($oauth->getMessages(), true));
                } catch (Exception $e) {
                    $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }
                try {
                    /** @var Ldap $ldap */
                    $ldap = $this->getEvent()->getApplication()->getServiceManager()->get(Ldap::class);
                    $ldap->updatePassword($payload['member_id'], $data['new_password']);
                    $this->ocsLog->info(__METHOD__ . ' - ldap : ' . var_export($ldap->getMessages(), true));
                } catch (Exception $e) {
                    $this->ocsLog->err($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                }

                $debugMsg .= '- password changed' . PHP_EOL;
                $this->ocsLog->debug($debugMsg);

                $this->flashMessenger()->addSuccessMessage('Your password is changed.');

                return $this->redirect()->toRoute('application_login');
            }
        }

        $this->layout()->setVariable('noheader', true);

        return new ViewModel(
            [
                'form'    => $form,
                'isError' => true,
            ]
        );
    }

    protected function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

}