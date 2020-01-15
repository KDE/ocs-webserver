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
 * Created: 27.06.2017
 */
class Default_Model_Solr
{

    public $_pagination = null;

    /**
     * @param $input
     * @return null|string|string[]
     */
    static public function escape($input)
    {
        $pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';

        return preg_replace($pattern, '\\\$1', $input);
    }

    /**
     * @param array $op
     * @return null
     * @throws Zend_Exception
     * @throws Zend_Paginator_Exception
     * @throws Zend_Service_Solr_HttpTransportException
     * @throws Zend_Service_Solr_InvalidArgumentException
     */
    public function search($op = array())
    {
        $output = null;

        $solr = $this->get_solr_connection();
        
        if (false === $solr->ping()) {           
            echo 'connection to solr server can not established';
            die;
            Zend_Registry::get('logger')->warn('connection to solr server can not established');
            return $output;
        }

        

        $params = array(
            'defType'           => 'dismax',
            'wt'                => 'json',
            'fl'                => '*,score',
            'df'                => 'title',
            'qf'                => empty($op['qf']) ? 'title_gen^5 title^5 title_prefix^5 description_gen description description_split username cat_title tags package_names arch_names license_names' : $op['qf'],
//          'bq'                => 'changed_at:[NOW-1YEAR TO NOW/DAY]',
//          'bf'                => 'if(lt(laplace_score,50),-10,10)',
//          'bf'                => 'product(recip(ms(NOW/HOUR,changed_at),3.16e-11,0.2,0.2),1300)',
//          'bf'                => 'product(recip(ms(NOW/HOUR,changed_at),3.16e-11,0.2,0.2),40)',
            'bf'                => 'product(recip(ms(NOW/HOUR,changed_at),3.16e-11,0.2,0.2),36)',    
//          'sort'              => 'changed_at desc',
            'hl'                => 'on',
            'hl.fl'             => 'description,tags,package_names,arch_names,license_names',
            'facet'             => 'true',
            'facet.field'       => array('project_category_id', 'tags','package_names','arch_names','license_names'),
            'facet.mincount'    => '1',
//          'facet.limit'       => '10',
            'facet.sort'        => 'count',
            'facet.range'       => 'laplace_score',
            'facet.range.start' => '0',
            'facet.range.end'   => '100',
            'facet.range.gap'   => '10',
            'spellcheck'        => 'true',
        );

    
        
        $params = $this->setStoreFilter($params,$op);
        $params = $this->addAnyFilter($params, $op);

        $offset = ((int)$op['page'] - 1) * (int)$op['count'];

        $query = trim($op['q']);



        if($offset<0) $offset = 0;
        $results = $solr->search($query, $offset, $op['count'], $params);

        $output['response'] = (array)$results->response;
        $output['responseHeader'] = (array)$results->responseHeader;
        $output['facet_counts'] = (array)$results->facet_counts;
        $output['facet_fields'] = (array)$results->facet_counts->facet_fields;
        $output['facet_ranges'] = (array)$results->facet_counts->facet_ranges;
        $output['hits'] = (array)$results->response->docs;
        //$output['highlighting'] =(array)$results->highlighting;
        $output['highlighting'] = json_decode(json_encode($results->highlighting), true);
        

        $pagination_array = array();
        if (isset($output['response']['numFound'])) {
            $pagination_array = array_combine(
                range(0, $output['response']['numFound'] - 1),
                range(1, $output['response']['numFound'])
            );
        }

        $pagination = Zend_Paginator::factory($pagination_array);
        $pagination->setCurrentPageNumber($op['page']);
        $pagination->setItemCountPerPage($op['count']);
        $pagination->setPageRange(9);

        $this->_pagination = $pagination;

        return $output;
    }

    public function isExist($project_id)
    {
        $solr = $this->get_solr_connection();
        $query = '*:*';
        $params = array('fq' => 'id:'.$project_id);
        $results = $solr->search($query, 0, 1, $params);
        if(sizeof($results->response->docs)==1)
        {
            return true;
        }
        else{
            return false;
        }

    }
    /**
     * @return Zend_Service_Solr
     * @throws Zend_Exception
     */
    private function get_solr_connection()
    {
        $config = Zend_Registry::get('config');
        $config_search = $config->settings->search;

        return new Zend_Service_Solr ($config_search->host, $config_search->port,
            $config_search->http_path); // Configure
    }

    /**
     * @param $params
     * @return mixed
     * @throws Zend_Exception
     */
    private function setStoreFilter($params,$op)
    {        
     
        // if(isset($op['store']))
        // {
        //     $storename = $op['store'];
        //     $storemodel = new Default_Model_DbTable_ConfigStore(); 
        //     $store = $storemodel->fetchDomainObjectsByName($storename);
        //     $currentStoreConfig = new Default_Model_ConfigStore($store['host']);
        // }
        // else
        // {
        //     $currentStoreConfig = Zend_Registry::get('store_config');                                  
        // }               

        // if (substr($currentStoreConfig->order, -1) <> 1) {
        //         return $params;
        // }
        // if(isset($currentStoreConfig->package_type)){            
        //     $pid = $currentStoreConfig->package_type;
        //     $t = new Default_Model_DbTable_Tags();
        //     $tag = $t->fetchRow($t->select()->where('tag_id='.$pid));           
        //     $params['fq'] = array_merge($params['fq'], array('package_names:' . $tag['tag_name']));   
        // }

        // $currentStoreConfig = Zend_Registry::get('store_config');  

        if(isset($op['store']))
        {
            $storename = $op['store'];
            $storemodel = new Default_Model_DbTable_ConfigStore(); 
            $store = $storemodel->fetchDomainObjectsByName($storename);
            $currentStoreConfig = new Default_Model_ConfigStore($store['host']);
        }
        else
        {
            $currentStoreConfig = Zend_Registry::get('store_config');                                  
        }  
        if($currentStoreConfig->store_id)
        {
            $params['fq'] = array('stores:(' . $currentStoreConfig->store_id . ')');        
        }        
        $csmodel  = new Default_Model_ConfigStoreTags();
        $packageFilter = $csmodel->getPackageTagsForStore($currentStoreConfig->store_id);       
        if($packageFilter)
        {
            $pkg = '';
            foreach ($packageFilter as $t) {
                $pkg=$pkg.' '.$t['tag_name'];
            }
            $pkg = trim($pkg);
            $params['fq'] = array_merge($params['fq'], array('package_names:(' . $pkg.')')); 
        }
        return $params;
    }

    /**
     * @param array $params
     * @param array $op
     *
     * @return array
     */
    private function addAnyFilter($params, $op)
    {
        if (empty($op['fq'])) {
            return $params;
        }
        if (empty($params['fq'])) {
            $params['fq'] = $op['fq'];
        } else {
            $params['fq'] = array_merge($params['fq'], $op['fq']);
        }

        return $params;
    }

    /**
     * @return null|Zend_Paginator
     * @throws Zend_Paginator_Exception
     */
    public function getPagination()
    {
        if (isset($this->_pagination)) {
            return $this->_pagination;
        }

        return Zend_Paginator::factory(array());
    }

    /**
     *
     * Get spell
     *
     * @param array $op
     * @return mixed
     * @throws Zend_Exception
     * @throws Zend_Service_Solr_HttpTransportException
     * @throws Zend_Service_Solr_InvalidArgumentException
     */
    public function spell($op = array())
    {
        $solr = $this->get_solr_connection();

        if (false === $solr->ping()) {
            Zend_Registry::get('logger')->warn('connection to solr server can not established');

            return $op['q'];
        }

        $results = $solr->spell($op['q']);
        $results = json_decode($results, true);

        return $results['spellcheck'];
    }

    /**
     * @param $object
     * @return array
     */
    private function object_to_array($object)
    {
        if (is_array($object) || is_object($object)) {
            $result = array();
            foreach ($object as $key => $value) {
                $result[$key] = $this->object_to_array($value);
            }

            return $result;
        }

        return $object;
    }

}