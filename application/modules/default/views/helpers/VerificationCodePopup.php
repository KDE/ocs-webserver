<?php
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
 **/

class Default_View_Helper_VerificationCodePopup extends Zend_View_Helper_Abstract
{

    public function verificationCodePopup($project_data, $link_1, $idPopupLayer, $view = null)
    {
        if (empty($link_1)) {
            return '';
        }

        if (isset($view)) {
            $this->view = $view;
        }

        $websiteOwner = new Local_Verification_WebsiteAuthCodeExist();
        $html_verifier = $websiteOwner->generateAuthCode(stripslashes($link_1));
        $this->view->inlineScript()->appendScript(
            '$(document).ready(function(){
                    ButtonCode.setupClipboardCopy(\'div#' . $idPopupLayer . ' #container-verification\');
                });'
        );

        return $this->getHTMLCode($project_data, $html_verifier, $idPopupLayer);

    }

    protected function getHTMLCode($project_data, $html_verifier, $idPopupLayer)
    {
        return '<div id="' . $idPopupLayer . '" class="modal fade" role="dialog" aria-labelledby="modalVerificationCodeLabel" aria-hidden="true">
                    <div class="modal-dialog pling-modal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 id="modalVerificationCodeLabel" class="modal-title center">' . $this->view->translate('Get Verification Code') . '</h3>
                            </div>
                            <div class="modal-body">
                                <div class="lightblue"></div>
                                <p class="light">' . $this->view->translate('Copy the meta tag below and paste it into your site home page. It should go in the &lt;head&gt; section, before the first &lt;body&gt; section.') . '</p>
                                <div class="center" id="container-verification">
                                    <div class="clipboard-copy clearfix embed-code">
                                        <div class="purple clipboard-code light span8" id="verification-code">
                                            &lt;meta name="ocs-site-verification" content="' . $html_verifier . '" /&gt;
                                        </div>
                                        <button class="btn btn-purple right light span4" data-clipboard-target="#verification-code">
                                            ' . $this->view->translate('COPY TO CLIPBOARD') . '
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a role="button" class="btn btn-light-grey verify partial" href="' . $this->view->buildProductUrl($project_data->project_id, 'verifycode') . '" data-target="#msg-box-code-product' . $project_data->project_id . ' .modal-body" >' . $this->view->translate('Verify my product page') . '</a>
                                <a role="button" class="btn btn-light-grey dismiss" href="#" data-dismiss="modal">' . $this->view->translate('Close') . '</a>
                            </div>
                        </div>
                    </div>
                </div>
                ';
    }

} 