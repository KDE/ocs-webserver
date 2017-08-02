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
 *
 * Created: 02.08.2017
 */

class Backend_Commands_SendCommentNotification implements Local_Queue_CommandInterface
{
    protected $template;
    protected $data;
    protected $comment;

    /**
     * Backend_Commands_SendNotification constructor.
     *
     * @param string   $template
     * @param stdClass $productData
     * @param string   $comment
     */
    public function __construct($template, $productData, $comment)
    {
        $this->template = $template;
        $this->data = $productData;
        $this->comment = $comment;
    }

    public function doCommand()
    {
        $newPasMail = new Default_Plugin_SendMail($this->template);
        $newPasMail->setReceiverMail($this->data->mail);
        $newPasMail->setReceiverAlias($this->data->username);

        $newPasMail->setTemplateVar('username', $this->data->username);
        $newPasMail->setTemplateVar('product_title', $this->data->title);
        $newPasMail->setTemplateVar('product_id', $this->data->project_id);
        $newPasMail->setTemplateVar('comment_text', $this->comment);

        $newPasMail->send();
    }

}