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

namespace JobQueue\Jobs;

/**
 * Class SendCommentNotification
 *
 * @package JobQueue\Jobs
 * @deprecated
 */
class SendCommentNotification extends BaseJob
{
    protected $template;
    protected $data;
    protected $comment;
    protected $mailer;

    /**
     * @param $args
     *
     * @see \Application\Controller\ProductcommentController::sendNotificationToOwner
     */
    public function perform($args)
    {
        var_export($args);
        $this->template = $args['template'];
        $this->productData = $args['productData'];
        $this->comment = $args['comment'];
        $this->mailer = $args['mailer'];
        $this->doCommand();
    }

    public function doCommand()
    {
        $newPasMail = $this->mailer;
        $newPasMail->setTemplate($this->template);
        $newPasMail->setReceiverMail($this->data->mail);
        $newPasMail->setReceiverAlias($this->data->username);

        $newPasMail->setTemplateVar('username', $this->data->username);
        $newPasMail->setTemplateVar('username_sender', $this->data->username_sender);
        $newPasMail->setTemplateVar('product_title', $this->data->title);
        $newPasMail->setTemplateVar('product_id', $this->data->project_id);
        $newPasMail->setTemplateVar('comment_text', $this->comment);

        $newPasMail->send();
    }

}