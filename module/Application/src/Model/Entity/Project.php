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

class Project implements InputFilterAwareInterface
{
    // attributes
    public $project_id;
    public $member_id;
    public $content_type;
    public $project_category_id;
    public $hive_category_id;
    public $is_active;
    public $is_deleted;
    public $status;
    public $uuid;
    public $pid;
    public $type_id;
    public $title;
    public $description;
    public $version;
    public $project_license_id;
    public $image_big;
    public $image_small;
    public $start_date;
    public $content_url;
    public $created_at;
    public $changed_at;
    public $major_updated_at;
    public $deleted_at;
    public $creator_id;
    public $facebook_code;
    public $twitter_code;
    public $google_code;
    public $source_url;
    public $link_1;
    public $embed_code;
    public $ppload_collection_id;
    public $validated;
    public $validated_at;
    public $featured;
    public $approved;
    public $ghns_excluded;
    public $spam_checked;
    public $pling_excluded;
    public $amount;
    public $amount_period;
    public $claimable;
    public $claimed_by_member;
    public $count_likes;
    public $count_dislikes;
    public $count_comments;
    public $count_downloads_hive;
    public $is_gitlab_project;
    public $gitlab_project_id;
    public $show_gitlab_project_issues;
    public $use_gitlab_project_readme;
    public $user_category;
    public $source_id;
    public $source_pk;
    public $source_type;

    /**
     * @param array $data
     */
    public function exchangeArray(array $data)
    {
        $this->project_id = !empty($data['project_id']) ? $data['project_id'] : null;
        $this->member_id = !empty($data['member_id']) ? $data['member_id'] : null;
        $this->content_type = !empty($data['content_type']) ? $data['content_type'] : null;
        $this->project_category_id = !empty($data['project_category_id']) ? $data['project_category_id'] : null;
        $this->hive_category_id = !empty($data['hive_category_id']) ? $data['hive_category_id'] : null;
        $this->is_active = !empty($data['is_active']) ? $data['is_active'] : null;
        $this->is_deleted = !empty($data['is_deleted']) ? $data['is_deleted'] : null;
        $this->status = !empty($data['status']) ? $data['status'] : null;
        $this->uuid = !empty($data['uuid']) ? $data['uuid'] : null;
        $this->pid = !empty($data['pid']) ? $data['pid'] : null;
        $this->type_id = !empty($data['type_id']) ? $data['type_id'] : null;
        $this->title = !empty($data['title']) ? $data['title'] : null;
        $this->description = !empty($data['description']) ? $data['description'] : null;
        $this->version = !empty($data['version']) ? $data['version'] : null;
        $this->project_license_id = !empty($data['project_license_id']) ? $data['project_license_id'] : null;
        $this->image_big = !empty($data['image_big']) ? $data['image_big'] : null;
        $this->image_small = !empty($data['image_small']) ? $data['image_small'] : null;
        $this->start_date = !empty($data['start_date']) ? $data['start_date'] : null;
        $this->content_url = !empty($data['content_url']) ? $data['content_url'] : null;
        $this->created_at = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->changed_at = !empty($data['changed_at']) ? $data['changed_at'] : null;
        $this->major_updated_at = !empty($data['major_updated_at']) ? $data['major_updated_at'] : null;
        $this->deleted_at = !empty($data['deleted_at']) ? $data['deleted_at'] : null;
        $this->creator_id = !empty($data['creator_id']) ? $data['creator_id'] : null;
        $this->facebook_code = !empty($data['facebook_code']) ? $data['facebook_code'] : null;
        $this->twitter_code = !empty($data['twitter_code']) ? $data['twitter_code'] : null;
        $this->google_code = !empty($data['google_code']) ? $data['google_code'] : null;
        $this->source_url = !empty($data['source_url']) ? $data['source_url'] : null;
        $this->link_1 = !empty($data['link_1']) ? $data['link_1'] : null;
        $this->embed_code = !empty($data['embed_code']) ? $data['embed_code'] : null;
        $this->ppload_collection_id = !empty($data['ppload_collection_id']) ? $data['ppload_collection_id'] : null;
        $this->validated = !empty($data['validated']) ? $data['validated'] : null;
        $this->validated_at = !empty($data['validated_at']) ? $data['validated_at'] : null;
        $this->featured = !empty($data['featured']) ? $data['featured'] : null;
        $this->approved = !empty($data['approved']) ? $data['approved'] : null;
        $this->ghns_excluded = !empty($data['ghns_excluded']) ? $data['ghns_excluded'] : null;
        $this->spam_checked = !empty($data['spam_checked']) ? $data['spam_checked'] : null;
        $this->pling_excluded = !empty($data['pling_excluded']) ? $data['pling_excluded'] : null;
        $this->amount = !empty($data['amount']) ? $data['amount'] : null;
        $this->amount_period = !empty($data['amount_period']) ? $data['amount_period'] : null;
        $this->claimable = !empty($data['claimable']) ? $data['claimable'] : null;
        $this->claimed_by_member = !empty($data['claimed_by_member']) ? $data['claimed_by_member'] : null;
        $this->count_likes = !empty($data['count_likes']) ? $data['count_likes'] : null;
        $this->count_dislikes = !empty($data['count_dislikes']) ? $data['count_dislikes'] : null;
        $this->count_comments = !empty($data['count_comments']) ? $data['count_comments'] : null;
        $this->count_downloads_hive = !empty($data['count_downloads_hive']) ? $data['count_downloads_hive'] : null;
        $this->is_gitlab_project = !empty($data['is_gitlab_project']) ? $data['is_gitlab_project'] : null;
        $this->gitlab_project_id = !empty($data['gitlab_project_id']) ? $data['gitlab_project_id'] : null;
        $this->show_gitlab_project_issues = !empty($data['show_gitlab_project_issues']) ? $data['show_gitlab_project_issues'] : null;
        $this->use_gitlab_project_readme = !empty($data['use_gitlab_project_readme']) ? $data['use_gitlab_project_readme'] : null;
        $this->user_category = !empty($data['user_category']) ? $data['user_category'] : null;
        $this->source_id = !empty($data['source_id']) ? $data['source_id'] : null;
        $this->source_pk = !empty($data['source_pk']) ? $data['source_pk'] : null;
        $this->source_type = !empty($data['source_type']) ? $data['source_type'] : null;

    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'project_id'                 => $this->project_id,
            'member_id'                  => $this->member_id,
            'content_type'               => $this->content_type,
            'project_category_id'        => $this->project_category_id,
            'hive_category_id'           => $this->hive_category_id,
            'is_active'                  => $this->is_active,
            'is_deleted'                 => $this->is_deleted,
            'status'                     => $this->status,
            'uuid'                       => $this->uuid,
            'pid'                        => $this->pid,
            'type_id'                    => $this->type_id,
            'title'                      => $this->title,
            'description'                => $this->description,
            'version'                    => $this->version,
            'project_license_id'         => $this->project_license_id,
            'image_big'                  => $this->image_big,
            'image_small'                => $this->image_small,
            'start_date'                 => $this->start_date,
            'content_url'                => $this->content_url,
            'created_at'                 => $this->created_at,
            'changed_at'                 => $this->changed_at,
            'major_updated_at'           => $this->major_updated_at,
            'deleted_at'                 => $this->deleted_at,
            'creator_id'                 => $this->creator_id,
            'facebook_code'              => $this->facebook_code,
            'twitter_code'               => $this->twitter_code,
            'google_code'                => $this->google_code,
            'source_url'                 => $this->source_url,
            'link_1'                     => $this->link_1,
            'embed_code'                 => $this->embed_code,
            'ppload_collection_id'       => $this->ppload_collection_id,
            'validated'                  => $this->validated,
            'validated_at'               => $this->validated_at,
            'featured'                   => $this->featured,
            'approved'                   => $this->approved,
            'ghns_excluded'              => $this->ghns_excluded,
            'spam_checked'               => $this->spam_checked,
            'pling_excluded'             => $this->pling_excluded,
            'amount'                     => $this->amount,
            'amount_period'              => $this->amount_period,
            'claimable'                  => $this->claimable,
            'claimed_by_member'          => $this->claimed_by_member,
            'count_likes'                => $this->count_likes,
            'count_dislikes'             => $this->count_dislikes,
            'count_comments'             => $this->count_comments,
            'count_downloads_hive'       => $this->count_downloads_hive,
            'is_gitlab_project'          => $this->is_gitlab_project,
            'gitlab_project_id'          => $this->gitlab_project_id,
            'show_gitlab_project_issues' => $this->show_gitlab_project_issues,
            'use_gitlab_project_readme'  => $this->use_gitlab_project_readme,
            'user_category'              => $this->user_category,
            'source_id'                  => $this->source_id,
            'source_pk'                  => $this->source_pk,
            'source_type'                => $this->source_type,
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