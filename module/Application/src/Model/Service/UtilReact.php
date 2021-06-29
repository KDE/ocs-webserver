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

use Application\View\Helper\AddDefaultScheme;
use DateTime;
use DateTimeZone;
use Exception;
use Laminas\Validator\Uri;

class UtilReact
{

    public static function cleanRatings(array $ratings)
    {
        if (empty($ratings)) {
            return $ratings;
        }
        $unwantedKeys = array(
            'project_id'    => 0,
            'user_like'     => 0,
            'user_dislike'  => 0,
            'score_test'    => 0,
            'comment_id'    => 0,
            'rating_active' => 0,
            'source_id'     => 0,
            'source_pk'     => 0,
        );
        $ratings = array_diff_key($ratings, $unwantedKeys);
        return $ratings;
    }

    public static function cleanChangelogs(array $changelog)
    {
        if (empty($changelog)) {
            return $changelog;
        }
        $wantedKeys = array(
            'title'      => 0,
            'created_at' => 0,
            'text'       => 0,
        );

        $changelog = array_intersect_key($changelog, $wantedKeys);

        return $changelog;
    }

     /**
     * @param array $productInfo
     *
     * @return array
     */
    public static function cleanProductBrowse(array $productInfo)
    {
        if (empty($productInfo)) {
            return $productInfo;
        }

        $wantedKeys = array(
            'project_id'           => 0,
            'member_id'            => 0,
            'project_category_id'  => 0,
            'title'                => 0,
            'description'          => 0,
            'version'              => 0,           
            'image_big'            => 0,
            'image_small'          => 0,
            'created_at'           => 0,
            'changed_at'           => 0,
            'major_updated_at'     => 0,
            'creator_id'           => 0,
            'ppload_collection_id' => 0,
            'featured'             => 0,
            'ghns_excluded'        => 0,
            'count_likes'          => 0,
            'count_comments'       => 0,
            'laplace_score'        => 0,
            'laplace_score_old'    => 0,
            'laplace_score_test'   => 0,
            'username'             => 0,
            'profile_image_url'    => 0,
            'cat_title'            => 0,
            'cat_xdg_type'         => 0,
            'cat_show_description' => 0,
            'count_plings'         => 0,
            'amount_reports'       => 0,
            'package_types'        => 0,
            'package_names'        => 0,
            'tags'                 => 0,
            'tag_ids'              => 0,
            'count_follower'       => 0,
            'tags_availablefor'       => 0,
        );
        $productInfo = array_intersect_key($productInfo, $wantedKeys);

        return $productInfo;
    }

    public static function cleanSupporter(array $supporter)
    {
        if (empty($supporter)) {
            return $supporter;
        }

        $wantedKeys = array(
            'member_id'           => 0,
            'username'            => 0,
            'profile_image_url'            => 0            
        );

        return array_intersect_key($supporter, $wantedKeys);
    }

    public static function cleanFile(array $file)
    {
        if (empty($file)) {
            return $file;
        }

        $wantedKeys = array(
            'id'           => 0,
            'active'           => 0,
            'owner_id'           => 0,
            'collection_id'           => 0,
            'name'           => 0,
            'type'           => 0,
            'size'           => 0,
            'md5sum'           => 0,
            'title'           => 0,
            'description'           => 0,
            'category'           => 0,
            'tags'           => 0,
            'version'           => 0,
            'ocs_compatible'           => 0,
            'created_timestamp'           => 0,
            'updated_timestamp'           => 0,
            'isInstall'           => 0,
            'count_dl_all_uk'           => 0,
            'count_dl_all_nouk'           => 0,
            'count_dl_uk_today'           => 0,
            'count_dl_today'           => 0,
            'count_dl_all'           => 0,
            'count_dl_today'           => 0,
            'downloaded_count'           => 0,
            'url_preview' => 0,
            'url_thumb' =>0,
            'ppload_file_preview_id' =>0,
            
                      
        );
     
        return array_intersect_key($file, $wantedKeys);
    }

     public static function cleanMainProject(array $p)
    {
        if (empty($p)) {
            return $p;
        }

        $wantedKeys = array(
            'description'           => 0,           
            
        );
     
        return array_intersect_key($p, $wantedKeys);
    }

    public static function cleanMember(array $p)
    {
        if (empty($p)) {
            return $p;
        }

        $wantedKeys = array(
            'member_id'           => 0,           
            'username'           => 0, 
            'avatar'           => 0,           
            'type'           => 0, 
            'is_active'           => 0,           
            'firstname'           => 0, 
            'lastname'           => 0,           
            'street'           => 0, 
            'zip'           => 0,           
            'city'           => 0, 
            'country'           => 0,           
            'last_online'           => 0, 
            'main_project_id'           => 0,           
            'profile_image_url'           => 0, 
            'link_facebook'           => 0,           
            'link_twitter'           => 0, 
            'link_website'           => 0,           
            'link_google'           => 0, 
            'created_at'           => 0, 
            'changed_at'           => 0, 
            'gitlab_user_id'           => 0,    
            // only for admin
            'pling_excluded'           => 0,          
            
        );
     
        return array_intersect_key($p, $wantedKeys);
    }

    
   
    
    /**
     * @param array $productInfo
     *
     * @return array
     */
    public static function cleanProductInfoForJson(array $productInfo)
    {
        if (empty($productInfo)) {
            return $productInfo;
        }

        $unwantedKeys = array(
            'roleId'           => 0,
            'mail'             => 0,
            'dwolla_id'        => 0,
            'paypal_mail'      => 0,
            'content_type'     => 0,
            'hive_category_id' => 0,
            'is_active'        => 0,
            'is_deleted'       => 0,
            'start_date'       => 0,
            'source_id'        => 0,
            'source_pk'        => 0,
            'source_type'      => 0,
            'uuid'      => 0,
            'content_url'      => 0,
            'major_updated_at'      => 0,
            'deleted_at'      => 0,
            'creator_id'      => 0,
            'facebook_code'      => 0,
            'twitter_code'      => 0,
            'google_code'      => 0,               
            'validated'      => 0,
            'validated_at'      => 0,
            'approved'      => 0,
            'pling_excluded'      => 0,
            'amount'      => 0,
            'amount_period'      => 0,
            'claimable'      => 0,
            'claimed_by_member'      => 0,
            'count_likes'      => 0,
            'count_dislikes'      => 0,
            'count_downloads_hive'      => 0,
            'show_gitlab_project_issues'      => 0,
            'use_gitlab_project_readme'      => 0,
            'user_category'      => 0,
            'project_validated'      => 0,           
            'project_member_id'      => 0,
            'project_source_pk'      => 0,                        
            'project_uuid'      => 0,            

            
        );

        $productInfo = array_diff_key($productInfo, $unwantedKeys);

        return $productInfo;
    }

    

    public static function purifyProduct($product)
    {        
        $product->title = strip_tags(HtmlPurifyService::purify($product->title));
        $product->description = BbcodeService::renderHtml(HtmlPurifyService::purify($product->description));        
        $product->version = strip_tags(HtmlPurifyService::purify($product->version));
        $ads = new AddDefaultScheme();    
        $product->link_1 = HtmlPurifyService::purify($ads($product->link_1), HtmlPurifyService::ALLOW_URL);
        $product->source_url = HtmlPurifyService::purify($product->source_url, HtmlPurifyService::ALLOW_URL);
        $product->facebook_code = HtmlPurifyService::purify($product->facebook_code, HtmlPurifyService::ALLOW_URL);
        $product->twitter_code = HtmlPurifyService::purify($product->twitter_code, HtmlPurifyService::ALLOW_URL);
        $product->google_code = HtmlPurifyService::purify($product->google_code, HtmlPurifyService::ALLOW_URL);    
        return $product;    
    }

    public static function cleanUserProducts(array $p)
    {
        if (empty($p)) {
            return $p;
        }
        $unwantedKeys = array(
            'count_likes'    => 0,
            'count_dislikes'     => 0,           
        );       
        return array_diff_key($p, $unwantedKeys);
    }

    public static function productImageSmall200($array)
    {
        foreach ($array as &$value) {
            $value['image_small'] = Util::image($value['image_small'], array('width' => 200, 'height' => 200)); 
        }
        return $array;
    }
}