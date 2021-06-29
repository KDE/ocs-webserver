<?php return array(
    'version'      => 'v1.94',
    'queue'        => array(
        'validate' => array(
            'name'           => 'website_validate',
            'dbAdapter'      => 'Local_Queue_Adapter_Db',
            'messageAdapter' => 'Local_Queue_Adapter_Db_Message',
            'maxMessages'    => '1',
        ),
    ),
    'third_party'  => array(
        'paypal' => array(
            'sandbox'                  => array(
                'active' => '0',
                'email'  => 'sanbaox@mail.com',
            ),
            'security'                 => array(
                'userid'    => 'ppuid',
                'password'  => 'pppass',
                'signature' => 'ppsig',
            ),
            'facilitator_fee_receiver' => 'receiver@mail.com',
            'facilitator_fee'          => '0',
            'application'              => array(
                'id' => 'APP-ID',
            ),
            'api'                      => array(
                'endpoint' => 'https://svcs.paypal.com',
            ),
            'form'                     => array(
                'endpoint' => 'https://www.paypal.com',
            ),
            'ipn'                      => array(
                'endpoint' => 'https://www.paypal.com',
            ),
            'masspay'                  => array(
                'endpoint' => 'https://api-3t.paypal.com/nvp',
                'ipn'      => array(
                    'endpoint' => 'https://ipnpb.paypal.com/cgi-bin',
                ),
            ),
            'service'                  => array(
                'version' => '1.2.0',
            ),
            'request'                  => array(
                'data' => array(
                    'format' => 'NV',
                ),
            ),
            'response'                 => array(
                'data' => array(
                    'format' => 'NV',
                ),
            ),
            'client'                   => array(
                'auth'           => 'Nocert',
                'application_id' => 'app',
                'partner_name'   => 'partner',
            ),
            'merchantid'               => '0',
            'test'                     => 'Live',
        ),
        'github' => array(
            'client_id'       => '',
            'client_secret'   => '',
            'client_callback' => $_SERVER['HTTP_HOST'] . '/oauth/github',
        ),
        'ppload' => array(
            'server'          => 'https://www.ocs-fileserver.org',
            'api_uri'         => 'https://www.ocs-fileserver.org/api/',
            'client_id'       => 'clientid',
            'secret'          => 'sec',
            'download_secret' => 'sec',
        ),
    ),
    'admin'        => array(
        'email' => 'contact@ocs-webserver.org',
    ),
    'website'      => array(
        'tracking' => array(
            'chartbeat' => '/js/tracking/chartbeat.js',
            'google'    => '/js/tracking/goggle.js',
        ),
    ),
    'settings'     => array(
        'search'        => array(
            'path'      => realpath(__DIR__ . '/../../data/indexes'),
            'host'      => 'localhost',
            'port'      => '8984',
            'http_path' => '/solr/any_core/',
        ),
        'spam_filter'   => array(
            'active' => '1',
        ),
        'double_opt_in' => array(
            'active' => '1',
        ),
        'noLESScompile' => '1',
        'savePageView'  => '1',
        'store'         => array(
            'template' => array(
                'path'    => realpath(__DIR__ . '/../../data/stores/templates/'),
                'default' => 'default',
            ),
        ),
        'queue'         => array(
            'general' => array(
                'name'          => 'ocs_jobs',
                'timeout'       => '600000',
                'message_count' => '1',
            ),
        ),
        'session'       => array(
            'filter_browse_original' => 'FilterBrowseOriginalSession',
            'anonymous_cookie_name'  => 'user_anonymous',
            'cookie_lifetime'        => '31536000',
        ),
        'client'        => array(
            'default' => array(
                'name'                              => 'default',
                'baseurl'                           => 'any-host.org',
                'baseurl_store'                     => 'www.any-host.com',
                'baseurl_meta'                      => 'any-host.org',
                'baseurl_member'                    => 'any-host.org',
                'baseurl_product'                   => 'any-host.org',
                'baselogo'                          => 'images/system/storeLogo.png',
                'url_forum'                         => '',
                'url_blog'                          => '',
                'url_gitlab'                        => '',
                'url_myopendesktop'                 => '',
                'url_cloudopendesktop'              => '',
                'url_musicopendesktop'              => '',
                'url_docsopendesktop'               => '',
                'url_mastodon'                      => '',
                'url_riot'                          => '',
                'riot_access_token'                 => '',
                'collection_cat_id'                 => '',
                'ranking_cat_id'                    => '',
                'tag_group_collection_type_id'      => '',
                'tag_collection_type_collection_id' => '',
                'tag_collection_type_ranking_id'    => '',
                'tag_group_original_id'             => '',
                'tag_original_id'                   => '',
                'tag_modification_id'               => '',
                'tag_group_ebook'                   => '',
                'tag_group_ebook_author'            => '',
                'tag_group_ebook_editor'            => '',
                'tag_group_ebook_illustrator'       => '',
                'tag_group_ebook_translator'        => '',
                'tag_group_ebook_subject'           => '',
                'tag_group_ebook_shelf'             => '',
                'tag_group_ebook_language'          => '',
                'tag_group_ebook_type'              => '',
                'tag_is_ebook'                      => '',
                'tag_group_osuser'                  => '',
                'tag_type_osuser'                   => '',
                'tag_group_dangerous_id'            => '',
                'tag_dangerous_id'                  => '',
            ),
        ),
        'static'        => array(
            'include_path' => realpath(__DIR__ . '/../../httpdocs/partials/'),
            'include'      => array(
                'contact'          => 'contact.phtml',
                'privacy'          => 'privacy.phtml',
                'imprint'          => 'imprint.phtml',
                'terms'            => 'terms.phtml',
                'terms-general'    => 'terms-general.phtml',
                'terms-publishing' => 'terms-publishing.phtml',
                'terms-payout'     => 'terms-payout.phtml',
                'terms-dmca'       => 'terms-dmca.phtml',
                'terms-cookies'    => 'terms-cookies.phtml',
                'faq'              => 'faq.phtml',
                'gitfaq'           => 'gitfaq.phtml',
                'faqold'           => 'faqold.phtml',
                'about'            => 'about.phtml',
                'ocsapi'           => 'ocsapi.phtml',
            ),
        ),
        'server'        => array(
            'images'     => array(
                'upload' => array(
                    'path' => realpath(__DIR__ . '/../../httpdocs/img/data/'),
                ),
                'media'  => array(
                    'server'     => 'http://cn.any_server.org',
                    'upload'     => 'http://cn.any_server.org/any_path',
                    'delete'     => 'http://cn.any_server.org/any_path',
                    'privateKey' => 'key',
                ),
            ),
            'videos'     => array(
                'upload' => array(
                    'path' => realpath(__DIR__ . '/../../httpdocs/video/data/'),
                ),
                'media'  => array(
                    'server'    => 'http://video.any_server.org',
                    'upload'    => 'http://video.any_server.org/any_path',
                    'cdnserver' => 'http://cdn.any_server.org/',
                ),
            ),
            'torrent'    => array(
                'media' => array(
                    'server'       => 'http://torrent.any_server.org',
                    'createurl'    => 'http://torrent.any_server.org/any_path',
                    'deleteurl'    => 'http://torrent.any_server.org/any_path',
                    'downloadurl'  => 'http://torrent.any_server.org/any_path',
                    'min_filesize' => '104857600',
                ),
            ),
            'comics'     => array(
                'media' => array(
                    'server'     => 'http://comic.any_server.org',
                    'extracturl' => 'http://comic.any_server.org/any_path',
                    'tocurl'     => 'http://comic.any_server.org/any_path',
                    'pageurl'    => 'http://comic.any_server.org/any_path',
                ),
            ),
            'files'      => array(
                'host'            => '',
                'download_secret' => '',
                'api'             => array(
                    'uri'           => '',
                    'client_id'     => '',
                    'client_secret' => '',
                    'mirror'        => '',
                ),
            ),
            'oauth'      => array(
                'host'             => '',
                'authorize_url'    => '',
                'token_url'        => '',
                'callback'         => $_SERVER['HTTP_HOST'] . '/oauth/ocs',
                'client_id'        => '',
                'client_secret'    => '',
                'create_user_url'  => '',
                'profile_user_url' => '',
                'user_agent'       => '',
            ),
            'opencode'   => array(
                'host'             => '',
                'user_logfilename' => 'opencode',
                'user_sudo'        => '',
                'user_agent'       => 'OCS Opendesktop',
                'private_token'    => '',
                'provider_name'    => 'oauth_opendesktop',
            ),
            'ldap'       => array(
                'host'                => '',
                'port'                => '389',
                'username'            => '',
                'password'            => '',
                'bindRequiresDn'      => '1',
                'accountDomainName'   => '',
                'userBaseDn'          => '',
                'groupBaseDn'         => '',
                'rootDn'              => '',
                'accountFilterFormat' => '(objectClass=account)',
                'tryUsernameSplit'    => '',
            ),
            // @deprecated
            'ldap_group' => array(
                'baseDn' => '',
            ),
            // @deprecated
            'ldap_ext'   => array(
                'rootDn' => '',
            ),
            'forum'      => array(
                'host'             => '',
                'user_logfilename' => 'forum',
                'user_sudo'        => '',
                'user_agent'       => '',
                'private_token'    => '',
            ),
            'chat'       => array(
                'host'             => '',
                'user_logfilename' => 'chat',
                'sudo_user'        => '',
                'sudo_user_pw'     => '',
                'home_server'      => '',
                'user_agent'       => '',
            ),
            'ip'         => array(
                'api' => array(
                    'v4' => '',
                    'v6' => '',
                ),
            ),
        ),
        'ocs_server'    => array(
            'apiUri' => 'http://ocs-server.org',
        ),
        'jwt'           => array(
            'secret'       => '',
            'expire'       => array(
                'accessToken'       => '2 hours',
                'refreshToken'      => '180 days',
                'cookie'            => '30 days',
                'authorizationCode' => '10 minutes',
                'resetCode'         => '2 hours',
            ),
            'issuer_ident' => 'http://localhost:80',
        ),
        'domain'        => array(
            'base'     => array(
                'host' => 'www.example.com',
            ),
            'forum'    => array(
                'host'        => 'forum.example.com',
                'cookie_name' => '_t',
            ),
            'openid'   => array(
                'host'        => 'id.example.com',
                'cookie_name' => 'ltat',
            ),
            'opencode' => array(
                'host'        => 'git.example.com',
                'cookie_name' => '_example.com_session',
            ),
            'mastodon' => array(
                'host' => 'mastodon.example.com',
            ),
        ),
        'validation'    => array(
            'rules' => array(
                'username' => '/^(?=.{4,20}$)(?![-])(?!.*[-]{2})[a-z0-9-]+(?<![-])$/',
                'login'    => '/^(?=.{3,40}$)(?![-])(?!.*[-]{2})[a-zA-Z0-9-]+(?<![-])$/',
                'email'    => '/^([a-zA-Z0-9_\-+\.]+)?@.*$/',
            ),
        ),
        'mail'          => array(
            'transport' => array(
                'withFileTransport' => true,
                'withSmtpTransport' => false,
                'host'              => "",
                'connection_class'  => 'plain',
                'username'          => "",
                'password'          => "",
                'ssl'               => 'tls',
            ),
            'defaults'  => array(
                'fromMail'    => '',
                'fromName'    => "",
                'replyToMail' => '',
                'replyToName' => '',
            ),
        ),
    ),
    'recaptcha'    => array(
        'sitekey'   => '',
        'secretkey' => '',
    ),
    'analytics'    => array(
        'google' => array(
            'enabled'    => false,
            'code'       => '',
            'default_id' => 0,
        ),
        'piwik' => array(
            'enabled'    => false,
            'code'       => '',
            'default_id' => 0,
        ),
    ),
    'experimental' => array(
        'piwik' => array(
            'stats_widget' => array(
                'enabled' => false,
            ),
        ),
    ),
);