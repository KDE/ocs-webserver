<?php


class Default_Plugin_Stats extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $allowed_controller = array('product', 'member');
        if (false == in_array($request->getControllerName(), $allowed_controller)) {
            return;
        }

        $session_stats = new Zend_Session_Namespace();
        foreach ($session_stats->getIterator()->getArrayCopy() as $key => $item) {
           Zend_Registry::get('logger')->debug(print_r($key, true).' => '.print_r($item,true));
        };
        $request_ip = $request->getClientIp();

        if (empty($session_stats->stat_ipv4) AND empty($session_stats->stat_ipv6)) {
            $session_stats->stat_valid = false;

            return;
        }

        if (($request_ip != $session_stats->stat_ipv4) AND ($request_ip != $session_stats->stat_ipv6)) {
            $session_stats->stat_valid = false;

            return;
        }
    }

}