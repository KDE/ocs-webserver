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

namespace Application\Model\Service;

class TagConst
{
    const TAG_TYPE_MEMBER = 2;
    const TAG_TYPE_OSUSER = 9;

    const TAG_USER_GROUPID = 5;
    const TAG_CATEGORY_GROUPID = 6;

    const TAG_LICENSE_GROUPID = 7;
    const TAG_PACKAGETYPE_GROUPID = 8;
    const TAG_ARCHITECTURE_GROUPID = 9;
    const TAG_GHNS_EXCLUDED_GROUPID = 10;

    const TAG_PRODUCT_ORIGINAL_GROUPID = 11;
    const TAG_PRODUCT_ORIGINAL_ID = 2451;

    const TAG_PRODUCT_EBOOK_GROUPID = 14;
    const TAG_PRODUCT_EBOOK_AUTHOR_GROUPID = 15;
    const TAG_PRODUCT_EBOOK_EDITOR_GROUPID = 16;
    const TAG_PRODUCT_EBOOK_ILLUSTRATOR_GROUPID = 17;
    const TAG_PRODUCT_EBOOK_TRANSLATOR_GROUPID = 18;
    const TAG_PRODUCT_EBOOK_SUBJECT_GROUPID = 19;
    const TAG_PRODUCT_EBOOK_SHELF_GROUPID = 20;
    const TAG_PRODUCT_EBOOK_LANGUAGE_GROUPID = 21;
    const TAG_PRODUCT_EBOOK_TYPE_GROUPID = 22;
    const TAG_PRODUCT_EBOOK_ID = 2532;

    const TAG_PROJECT_GROUP_IDS = '6,7,10';//type product : category-tags, license-tags,ghns_excluded
    const TAG_FILE_GROUP_IDS = '8,9';//file-packagetype-tags,file-architecture-tags

    // From TagsRepository const.
    const TAG_TYPE_PROJECT = 1;
    const TAG_TYPE_FILE = 3;
    const TAG_GROUP_USER = 5;
    const TAG_GROUP_CATEGORY = 6;
    const TAG_GROUP_LICENSE = 7;
    const TAG_GROUP_PACKAGETYPE = 8;
    const TAG_GROUP_ARCHITECTURE = 9;
    const TAG_GROUP_GHNS_EXCLUDED = 10;
    const TAG_GHNS_EXCLUDED_ID = 1529;
}