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

namespace Application\Model\Service;

use Application\Model\Interfaces\MailTemplateInterface;
use Application\Model\Service\Entity\MailEntity;
use Application\Model\Service\Interfaces\MailerInterface;
use Exception;
use Laminas\Mail;

class Mailer extends BaseService implements MailerInterface
{

    private $_bodyText;
    private $_subject;
    private $_receiverMail;
    private $_receiverAlias;
    private $_fromAlias = "opendesktop.org";
    private $_fromMail = "contact@opendesktop.org";
    private $_tplVars = array();

    private $mailTemplateRepository;
    private $log;
    /**
     * @var Mail\Transport\TransportInterface
     */
    private $transport;

    function __construct(
        MailTemplateInterface $mailTemplateRepository
    ) {
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->log = $GLOBALS['ocs_log'];
    }

    /**
     * @param string $recipients
     *
     * @return string
     */
    public static function generateEmailFilename($recipients)
    {
        return 'mail_' . time() . '_' . $recipients . '.eml';
    }

    public function setTemplate($tplName)
    {
        if ($tplName === null) {
            return;
        }

        $mailTpl = $this->mailTemplateRepository->findBy('name', $tplName);

        $this->setSubject($mailTpl->subject);
        $this->setBodyText($mailTpl->text);
    }

    public function setTemplateVar($key, $value)
    {
        $this->_tplVars[$key] = $value;
    }

    public function getBodyText()
    {
        return $this->_bodyText;
    }

    public function setBodyText($bodyText)
    {
        $this->_bodyText = $bodyText;
    }

    public function setTransport(Mail\Transport\TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function send()
    {
        try {
            $mail = new Mail\Message();
            $mail->setEncoding('utf-8');
            $mail->setBody($this->buildBodyHtml());
            $mail->setFrom($this->getFromMail(), $this->getFromAlias());

            $mail->addTo($this->getReceiverMail());
            $mail->setSubject($this->getSubject());

            $this->transport->send($mail);

            $this->log->info(__METHOD__ . " - mail address: {$this->getReceiverMail()} - subject : {$this->getSubject()} - email sent.");
        } catch (Exception $e) {
            $this->log->err(__METHOD__ . " - mail address: {$this->getReceiverMail()} " . $e->getMessage() . PHP_EOL);
        }
    }

    private function buildBodyHtml()
    {
        $textReplacedVars = $this->replaceVars();
        $returnBodyText = <<< EOT
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>ocs. - open content store.</title>
        <style type="text/css">
			/* /\/\/\/\/\/\/\/\/ CLIENT-SPECIFIC STYLES /\/\/\/\/\/\/\/\/ */
			#outlook a{padding:0;} /* Force Outlook to provide a "view in browser" message */
			.ReadMsgBody{width:100%;} .ExternalClass{width:100%;} /* Force Hotmail to display emails at full width */
			.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing */
			body, table, td, p, a, li, blockquote{-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;} /* Prevent WebKit and Windows mobile changing default text sizes */
			table, td{mso-table-lspace:0pt; mso-table-rspace:0pt;} /* Remove spacing between tables in Outlook 2007 and up */
			img{-ms-interpolation-mode:bicubic;} /* Allow smoother rendering of resized image in Internet Explorer */

			/* /\/\/\/\/\/\/\/\/ RESET STYLES /\/\/\/\/\/\/\/\/ */
			body{margin:0; padding:0;}
			img{border:0; height:auto; line-height:100%; outline:none; text-decoration:none;}
			table{border-collapse:collapse !important;}
			body, #bodyTable, #bodyCell{height:100% !important; margin:0; padding:0; width:100% !important;}

			/* /\/\/\/\/\/\/\/\/ TEMPLATE STYLES /\/\/\/\/\/\/\/\/ */

			/* ========== Page Styles ========== */

			#bodyCell{padding:20px;}
			#templateContainer{width:600px;}

			/**
             * @tab Page
             * @section background style
             * @tip Set the background color and top border for your email. You may want to choose colors that match your company's branding.
             * @theme page
             */
			body, #bodyTable{
				/*@editable*/ background-color:#DEE0E2;
			}

    /**
     * @tab Page
     * @section background style
     * @tip Set the background color and top border for your email. You may want to choose colors that match your company's branding.
     * @theme page
     */
    #bodyCell{
    /*@editable*/ border-top:4px solid #BBBBBB;
}

/**
 * @tab Page
 * @section email border
 * @tip Set the border for your email.
 */
#templateContainer{
/*@editable*/ border:1px solid #BBBBBB;
			}

			/**
             * @tab Page
             * @section heading 1
             * @tip Set the styling for all first-level headings in your emails. These should be the largest of your headings.
             * @style heading 1
             */
			h1{
    /*@editable*/ color:#202020 !important;
    display:block;
    /*@editable*/ font-family:"Lato", Helvetica, Arial, sans-serif;
				/*@editable*/ font-size:26px;
				/*@editable*/ font-style:normal;
				/*@editable*/ font-weight:bold;
				/*@editable*/ line-height:100%;
				/*@editable*/ letter-spacing:normal;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				/*@editable*/ text-align:left;
			}

			/**
             * @tab Page
             * @section heading 2
             * @tip Set the styling for all second-level headings in your emails.
             * @style heading 2
             */
			h2{
    /*@editable*/ color:#404040 !important;
    display:block;
    /*@editable*/ font-family:"Lato", Helvetica, Arial, sans-serif;
				/*@editable*/ font-size:20px;
				/*@editable*/ font-style:normal;
				/*@editable*/ font-weight:bold;
				/*@editable*/ line-height:100%;
				/*@editable*/ letter-spacing:normal;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				/*@editable*/ text-align:left;
			}

			/**
             * @tab Page
             * @section heading 3
             * @tip Set the styling for all third-level headings in your emails.
             * @style heading 3
             */
			h3{
    /*@editable*/ color:#606060 !important;
    display:block;
    /*@editable*/ font-family:"Lato", Helvetica, Arial, sans-serif;
				/*@editable*/ font-size:16px;
				/*@editable*/ font-style:italic;
				/*@editable*/ font-weight:normal;
				/*@editable*/ line-height:100%;
				/*@editable*/ letter-spacing:normal;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				/*@editable*/ text-align:left;
			}

			/**
             * @tab Page
             * @section heading 4
             * @tip Set the styling for all fourth-level headings in your emails. These should be the smallest of your headings.
             * @style heading 4
             */
			h4{
    /*@editable*/ color:#808080 !important;
    display:block;
    /*@editable*/ font-family:"Lato", Helvetica, Arial, sans-serif;
				/*@editable*/ font-size:14px;
				/*@editable*/ font-style:italic;
				/*@editable*/ font-weight:normal;
				/*@editable*/ line-height:100%;
				/*@editable*/ letter-spacing:normal;
				margin-top:0;
				margin-right:0;
				margin-bottom:10px;
				margin-left:0;
				/*@editable*/ text-align:left;
			}

			/* ========== Header Styles ========== */

			/**
             * @tab Header
             * @section preheader style
             * @tip Set the background color and bottom border for your email's preheader area.
             * @theme header
             */
			#templatePreheader{
				/*@editable*/ background-color:#34495c;
				/*@editable*/ border-bottom:1px solid #CCCCCC;
			}

			/**
             * @tab Header
             * @section preheader text
             * @tip Set the styling for your email's preheader text. Choose a size and color that is easy to read.
             */
			.preheaderContent{
    /*@editable*/ color:#ffffff;
    /*@editable*/ font-family:"Lato", Helvetica, Arial, sans-serif;
				/*@editable*/ font-size:12px;
				/*@editable*/ line-height:125%;
				/*@editable*/ text-align:right;
			}

			/**
             * @tab Header
             * @section preheader link
             * @tip Set the styling for your email's preheader links. Choose a color that helps them stand out from your text.
             */
			.preheaderContent a:link, .preheaderContent a:visited, /* Yahoo! Mail Override */ .preheaderContent a .yshortcuts /* Yahoo! Mail Override */{
    /*@editable*/ color:#606060;
    /*@editable*/ font-weight:normal;
				/*@editable*/ text-decoration:underline;
			}

			/**
             * @tab Header
             * @section header style
             * @tip Set the background color and borders for your email's header area.
             * @theme header
             */
			#templateHeader{
				/*@editable*/ background-color:#F4F4F4;
				/*@editable*/ border-top:1px solid #FFFFFF;
				/*@editable*/ border-bottom:1px solid #CCCCCC;
			}

			/**
             * @tab Header
             * @section header text
             * @tip Set the styling for your email's header text. Choose a size and color that is easy to read.
             */
			.headerContent{
    /*@editable*/ color:#505050;
    /*@editable*/ font-family:"Lato", Helvetica, Arial, sans-serif;
				/*@editable*/ font-size:20px;
				/*@editable*/ font-weight:bold;
				/*@editable*/ line-height:100%;
				/*@editable*/ padding-top:0;
				/*@editable*/ padding-right:0;
				/*@editable*/ padding-bottom:0;
				/*@editable*/ padding-left:0;
				/*@editable*/ text-align:left;
				/*@editable*/ vertical-align:middle;
			}

			/**
             * @tab Header
             * @section header link
             * @tip Set the styling for your email's header links. Choose a color that helps them stand out from your text.
             */
			.headerContent a:link, .headerContent a:visited, /* Yahoo! Mail Override */ .headerContent a .yshortcuts /* Yahoo! Mail Override */{
    /*@editable*/ color:#EB4102;
    /*@editable*/ font-weight:normal;
				/*@editable*/ text-decoration:underline;
			}

			#headerImage{
				height:auto;
				max-width:600px;
			}

			/* ========== Body Styles ========== */

			/**
             * @tab Body
             * @section body style
             * @tip Set the background color and borders for your email's body area.
             */
			#templateBody{
				/*@editable*/ background-color:#F4F4F4;
				/*@editable*/ border-top:1px solid #FFFFFF;
				/*@editable*/ border-bottom:1px solid #CCCCCC;
			}

			/**
             * @tab Body
             * @section body text
             * @tip Set the styling for your email's main content text. Choose a size and color that is easy to read.
             * @theme main
             */
			.bodyContent{
    /*@editable*/ color:#505050;
    /*@editable*/ font-family:"Lato", Helvetica, Arial, sans-serif;
				/*@editable*/ font-size:14px;
				/*@editable*/ line-height:150%;
				padding-top:20px;
				padding-right:20px;
				padding-bottom:20px;
				padding-left:20px;
				/*@editable*/ text-align:left;
			}

			/**
             * @tab Body
             * @section body link
             * @tip Set the styling for your email's main content links. Choose a color that helps them stand out from your text.
             */
			.bodyContent a:link, .bodyContent a:visited, /* Yahoo! Mail Override */ .bodyContent a .yshortcuts /* Yahoo! Mail Override */{
    /*@editable*/ color:#EB4102;
    /*@editable*/ font-weight:normal;
				/*@editable*/ text-decoration:underline;
			}

			.bodyContent img{
    display:inline;
    height:auto;
    max-width:560px;
			}

			/* ========== Footer Styles ========== */

			/**
             * @tab Footer
             * @section footer style
             * @tip Set the background color and borders for your email's footer area.
             * @theme footer
             */
			#templateFooter{
				/*@editable*/ background-color:#34495c;
				/*@editable*/ border-top:1px solid #FFFFFF;
			}

			/**
             * @tab Footer
             * @section footer text
             * @tip Set the styling for your email's footer text. Choose a size and color that is easy to read.
             * @theme footer
             */
			.footerContent{
    /*@editable*/ color:#ffffff;
    /*@editable*/ font-family:"Lato", Helvetica, Arial, sans-serif;
				/*@editable*/ font-size:10px;
				/*@editable*/ line-height:150%;
				padding-top:20px;
				padding-right:20px;
				padding-bottom:20px;
				padding-left:20px;
				/*@editable*/ text-align:left;
			}

			/**
             * @tab Footer
             * @section footer link
             * @tip Set the styling for your email's footer links. Choose a color that helps them stand out from your text.
             */
			.footerContent a:link, .footerContent a:visited, /* Yahoo! Mail Override */ .footerContent a .yshortcuts, .footerContent a span /* Yahoo! Mail Override */{
    /*@editable*/ color:#ffffff;
    /*@editable*/ font-weight:normal;
				/*@editable*/ text-decoration:underline;
			}

			/* /\/\/\/\/\/\/\/\/ MOBILE STYLES /\/\/\/\/\/\/\/\/ */

            @media only screen and (max-width: 480px){
    /* /\/\/\/\/\/\/ CLIENT-SPECIFIC MOBILE STYLES /\/\/\/\/\/\/ */
    body, table, td, p, a, li, blockquote{-webkit-text-size-adjust:none !important;} /* Prevent Webkit platforms from changing default text sizes */
                body{width:100% !important; min-width:100% !important;} /* Prevent iOS Mail from adding padding to the body */

				/* /\/\/\/\/\/\/ MOBILE RESET STYLES /\/\/\/\/\/\/ */
				#bodyCell{padding:10px !important;}

				/* /\/\/\/\/\/\/ MOBILE TEMPLATE STYLES /\/\/\/\/\/\/ */

				/* ======== Page Styles ======== */

				/**
                 * @tab Mobile Styles
                 * @section template width
                 * @tip Make the template fluid for portrait or landscape view adaptability. If a fluid layout doesn't work for you, set the width to 300px instead.
                 */
				#templateContainer{
					max-width:600px !important;
					/*@editable*/ width:100% !important;
				}

				/**
                 * @tab Mobile Styles
                 * @section heading 1
                 * @tip Make the first-level headings larger in size for better readability on small screens.
                 */
				h1{
    /*@editable*/ font-size:24px !important;
					/*@editable*/ line-height:100% !important;
				}

				/**
                 * @tab Mobile Styles
                 * @section heading 2
                 * @tip Make the second-level headings larger in size for better readability on small screens.
                 */
				h2{
    /*@editable*/ font-size:20px !important;
					/*@editable*/ line-height:100% !important;
				}

				/**
                 * @tab Mobile Styles
                 * @section heading 3
                 * @tip Make the third-level headings larger in size for better readability on small screens.
                 */
				h3{
    /*@editable*/ font-size:18px !important;
					/*@editable*/ line-height:100% !important;
				}

				/**
                 * @tab Mobile Styles
                 * @section heading 4
                 * @tip Make the fourth-level headings larger in size for better readability on small screens.
                 */
				h4{
    /*@editable*/ font-size:16px !important;
					/*@editable*/ line-height:100% !important;
				}

				/* ======== Header Styles ======== */

				#templatePreheader{display:none !important;} /* Hide the template preheader to save space */

				/**
                 * @tab Mobile Styles
                 * @section header image
                 * @tip Make the main header image fluid for portrait or landscape view adaptability, and set the image's original width as the max-width. If a fluid setting doesn't work, set the image width to half its original size instead.
                 */
				#headerImage{
					height:auto !important;
					/*@editable*/ max-width:600px !important;
					/*@editable*/ width:100% !important;
				}

				/**
                 * @tab Mobile Styles
                 * @section header text
                 * @tip Make the header content text larger in size for better readability on small screens. We recommend a font size of at least 16px.
                 */
				.headerContent{
    /*@editable*/ font-size:20px !important;
					/*@editable*/ line-height:125% !important;
				}

				/* ======== Body Styles ======== */

				/**
                 * @tab Mobile Styles
                 * @section body text
                 * @tip Make the body content text larger in size for better readability on small screens. We recommend a font size of at least 16px.
                 */
				.bodyContent{
    /*@editable*/ font-size:18px !important;
					/*@editable*/ line-height:125% !important;
				}

				/* ======== Footer Styles ======== */

				/**
                 * @tab Mobile Styles
                 * @section footer text
                 * @tip Make the body content text larger in size for better readability on small screens.
                 */
				.footerContent{
    /*@editable*/ font-size:14px !important;
					/*@editable*/ line-height:115% !important;
				}

				.footerContent a{display:block !important;} /* Place footer social and utility links on their own lines, for easier access */
			}
		</style>
    </head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    	<center>
        	<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
            	<tr>
                	<td align="center" valign="top" id="bodyCell">
                    	<!-- BEGIN TEMPLATE // -->
                        <table border="0" cellpadding="0" cellspacing="0" id="templateContainer">
                        	<tr>
                            	<td align="center" valign="top">
                                </td>
                            </tr>
                        	<tr>
                            	<td align="center" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateBody">
                                        <tr>
                                            <td valign="top" class="bodyContent">

                                            $textReplacedVars

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        	<tr>
                            	<td align="center" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateFooter">
                                        <tr>
                                            <td valign="top" class="footerContent">
                                                <a href="https://twitter.com/opendesktop">Follow on Twitter</a>&nbsp;&nbsp;&nbsp;<a href="https://www.facebook.com/opendesktop.org">Follow on Facebook</a>&nbsp;
                                            </td>
                                        </tr>
                                        <tr>
                                            <td valign="top" class="footerContent" style="padding-top:0;">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>
EOT;

        return $returnBodyText;
    }

    private function replaceVars()
    {
        if (empty($this->_bodyText)) {
            return '';
        }

        $suchmuster = array_map(
            function ($n) {
                return '/%' . $n . '%/';
            }, array_keys($this->_tplVars)
        );
        $ersetzung = array_values($this->_tplVars);

        return preg_replace($suchmuster, $ersetzung, $this->_bodyText);
    }

    public function getFromMail()
    {
        return $this->_fromMail;
    }

    public function setFromMail($mail)
    {
        $this->_fromMail = $mail;
    }

    public function getFromAlias()
    {
        return $this->_fromAlias;
    }

    public function setFromAlias($alias)
    {
        $this->_fromAlias = $alias;
    }

    public function getReceiverMail()
    {
        return $this->_receiverMail;
    }

    public function setReceiverMail($mail)
    {
        $this->_receiverMail = $mail;
    }

    public function getSubject()
    {
        if (empty($this->_subject)) {
            return '';
        }

        $suchmuster = array_map(
            function ($n) {
                return '/%' . $n . '%/';
            }, array_keys($this->_tplVars)
        );
        $ersetzung = array_values($this->_tplVars);

        return preg_replace($suchmuster, $ersetzung, $this->_subject);
    }

    public function setSubject($subject)
    {
        $this->_subject = $subject;
    }

    public function getHtmlMailEntity()
    {
        $mail = new MailEntity();
        $mail->setBody($this->getBodyHtml());
        $mail->setFromAlias($this->getFromAlias());
        $mail->setFromMail($this->getFromMail());
        $mail->setReceiverMail($this->getReceiverMail());
        $mail->setReceiverAlias($this->getReceiverAlias());
        $mail->setSubject($this->getSubject());

        return $mail;
    }

    public function getBodyHtml()
    {
        return $this->buildBodyHtml();
    }

    public function getReceiverAlias()
    {
        return $this->_receiverAlias;
    }

    public function setReceiverAlias($alias)
    {
        $this->_receiverAlias = $alias;
    }
}