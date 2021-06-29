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

namespace Application\Model\Entity;

use DomainException;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class ProjectCategoryData implements InputFilterAwareInterface
{
    // attributes
    public $project_category_id;
    public $lft;
    public $rgt;
    public $title;
    public $is_active;
    public $is_deleted;
    public $xdg_type;
    public $name_legacy;
    public $orderPos;
    public $dl_pling_factor;
    public $mv_pling_factor;
    public $show_description;
    public $source_required;
    public $browse_list_type;
    public $tag_rating;
    public $created_at;
    public $changed_at;
    public $deleted_at;

    public $parent_active;
    public $title_show;
    public $title_legacy;
    public $depth;
    public $ancestor_id_path;
    public $ancestor_path;
    public $ancestor_path_legacy;
    public $parent;

    public function exchangeArray(array $data)
    {
        $this->project_category_id = !empty($data['project_category_id']) ? $data['project_category_id'] : null;
        $this->lft = !empty($data['lft']) ? $data['lft'] : null;
        $this->rgt = !empty($data['rgt']) ? $data['rgt'] : null;
        $this->title = !empty($data['title']) ? $data['title'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->is_deleted = !empty($data['is_deleted']) ? $data['is_deleted'] : null;
        $this->xdg_type = !empty($data['xdg_type']) ? $data['xdg_type'] : null;
        $this->name_legacy = !empty($data['name_legacy']) ? $data['name_legacy'] : null;
        $this->orderPos = !empty($data['orderPos']) ? $data['orderPos'] : null;
        $this->dl_pling_factor = !empty($data['dl_pling_factor']) ? $data['dl_pling_factor'] : null;
        $this->mv_pling_factor = !empty($data['mv_pling_factor']) ? $data['mv_pling_factor'] : null;
        $this->show_description = !empty($data['show_description']) ? $data['show_description'] : null;
        $this->source_required = !empty($data['source_required']) ? $data['source_required'] : null;
        $this->browse_list_type = !empty($data['browse_list_type']) ? $data['browse_list_type'] : null;
        $this->tag_rating = !empty($data['tag_rating']) ? $data['tag_rating'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;

        $this->parent_active = !empty($data['parent_active']) ? $data['parent_active'] : null;
        $this->title_show = !empty($data['title_show']) ? $data['title_show'] : null;
        $this->title_legacy = !empty($data['title_legacy']) ? $data['title_legacy'] : null;
        $this->depth = !empty($data['depth']) ? $data['depth'] : null;
        $this->ancestor_id_path = !empty($data['ancestor_id_path']) ? $data['ancestor_id_path'] : null;
        $this->ancestor_path = !empty($data['ancestor_path']) ? $data['ancestor_path'] : null;
        $this->ancestor_path_legacy = !empty($data['ancestor_path_legacy']) ? $data['ancestor_path_legacy'] : null;
        $this->parent = !empty($data['parent']) ? $data['parent'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'project_category_id' => $this->project_category_id,
            'lft'                 => $this->lft,
            'rgt'                 => $this->rgt,
            'title'               => $this->title,
            'is_active'           => $this->is_active,
            'is_deleted'          => $this->is_deleted,
            'xdg_type'            => $this->xdg_type,
            'name_legacy'         => $this->name_legacy,
            'orderPos'            => $this->orderPos,
            'dl_pling_factor'     => $this->dl_pling_factor,
            'mv_pling_factor'     => $this->mv_pling_factor,
            'show_description'    => $this->show_description,
            'source_required'     => $this->source_required,
            'browse_list_type'    => $this->browse_list_type,
            'tag_rating'          => $this->tag_rating,
            'created_at'          => $this->created_at,
            'changed_at'          => $this->changed_at,
            'deleted_at'          => $this->deleted_at,

            'parent_active'        => $this->parent_active,
            'title_show'           => $this->title_show,
            'title_legacy'         => $this->title_legacy,
            'depth'                => $this->depth,
            'ancestor_id_path'     => $this->ancestor_id_path,
            'ancestor_path'        => $this->ancestor_path,
            'ancestor_path_legacy' => $this->ancestor_path_legacy,
            'parent'               => $this->parent,
        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf('%s does not allow injection of an alternate input filter', __CLASS__));
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();
        //-----------------> hier come inputfiler
        $this->inputFilter = $inputFilter;

        return $this->inputFilter;
    }
}