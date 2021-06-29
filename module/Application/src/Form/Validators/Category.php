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
 */

namespace Application\Form\Validators;

use Application\Model\Repository\ProjectCategoryRepository;
use Laminas\Validator\AbstractValidator;

/**
 * Class Category
 *
 * @package Application\Form\Validators
 */
class Category extends AbstractValidator
{

    const ERROR_CAT_NOT_THE_LAST = 'cat_not_last';

    protected $projectCategoryRepository;
    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_CAT_NOT_THE_LAST => "Please select a children for this category.",
    );

    public function __construct(ProjectCategoryRepository $projectCategoryRepository)
    {
        $this->projectCategoryRepository = $projectCategoryRepository;
    }

    /**
     *  This method will check if the given cat_id has no children and is the last in hierarchy
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public function isValid($value)
    {
        $valid = true;
        //$this->_setValue($value);

        $tableCat = $this->projectCategoryRepository;
        $catChildIds = $tableCat->fetchChildIds($value);
        if (count($catChildIds)) {
            $valid = false;
            $this->setMessage(self::ERROR_CAT_NOT_THE_LAST);
        }

        return $valid;
    }

}