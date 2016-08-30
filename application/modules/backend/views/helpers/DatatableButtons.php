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
class Backend_View_Helper_DatatableButtons extends Zend_View_Helper_Abstract
{

    public function datatableButtons($id = "", $button = "", $path = "")
    {
        $button = trim($button);

        if ($path != "#") {
            $path = $path . $id;
        }
        switch ($button) {
            case 'preview':
                $this->getPreviewButton($id);
                break;
            case 'edit':

                $this->getEditButton($id, $path);
                break;
            case 'activate':
                $this->getActivateButton($id);
                break;
            case 'deactivate':
                $this->getDeactivateButton($id);
                break;
            case 'delete':
                $this->getDeleteButton($id);
                break;
            case 'add':
                $this->getAddButton($id, $path);
                break;
            case 'link':
                $this->getTagButton($id, $path);
                break;
            case 'wrench':
                $this->getWrenchButton($id, $path);
                break;
            case 'image':
                $this->getImageButton($id, $path);
                break;
            case 'newdate':
                $this->getDateButton($id, $path);
                break;
            case 'close':
                $this->getCloseButton($id, $path);
                break;
        }

    }

    function getPreviewButton($id)
    {
        print "<a href='#' title='vorschau' class='previewLink'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-document'></span></div></a>\n";
    }

    function getEditButton($id, $path = "#")
    {
        print "<a href='" . $path . "' title='bearbeiten'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></div></a>\n";
    }

    function getActivateButton($id)
    {
        print "<a href='#' title='deaktivieren' style='display: none;'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-cancel'></span></div></a>\n";
        print "<a href='#' title='aktivieren'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-check'></span></div></a>\n";
    }

    function getDeactivateButton($id)
    {
        print "<a href='#' title='deaktivieren'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-cancel'></span></div></a>\n";
        print "<a href='#' title='aktivieren' style='display: none;'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-check'></span></div></a>\n";
    }

    function getDeleteButton($id)
    {
        print "<a href='#' title='l&ouml;schen'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-trash'></span></div></a>\n";
    }

    function getAddButton($id, $path = "#")
    {
        print "<a href='$path' title='hinzuf&uuml;gen'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-plus'></span></div></a>\n";
    }

    function getTagButton($id, $path = "#")
    {
        print "<a href='$path' title='Mit Seite verknüpfen'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-link'></span></div></a>\n";
    }

    function getWrenchButton($id, $path = "#")
    {
        print "<a href='$path' title='Seitenverknüpfung ändern'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-wrench'></span></div></a>\n";
    }

    function getImageButton($id, $path = "#")
    {
        print "<a href='$path' title='bilder anzeigen'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-image'></span></div></a>\n";
    }

    function getDateButton($id, $path = "#")
    {
        print "<a href='$path' title='Neuen Termin eintragen'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-calendar'></span></div></a>\n";
    }

    function getCloseButton($id, $path = "#")
    {
        print "<a href='$path' title='Ist ausgebucht'><div id='" . $id . "' class='alexIcon ui-state-default ui-corner-all'><span class='ui-icon ui-icon-closethick'></span></div></a>\n";
    }

}