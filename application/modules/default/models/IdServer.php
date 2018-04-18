<?php

class Default_Model_IdServer
{
    private $id_server;

    /**
     * @inheritDoc
     */
    public function __construct($id_server = null)
    {
        if (isset($id_server)) {
            $this->id_server = $id_server;
        } else {
            $config = Zend_Registry::get('config')->settings->id_server;
            $this->id_server = new Default_Model_Id_OcsServer($config);
        }
    }

    public function createUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $data = $this->mapUserData($member_id);

        return $this->id_server->pushHttpUserData($data);
    }

    /**
     * @param $member_id
     * @return array
     * @throws Zend_Exception
     */
    protected function mapUserData($member_id)
    {
        $user = $this->getUserData($member_id);

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - $user:' . print_r($user->toArray(), true));

        $data = array(
            'ocs_user_id'    => $user->member_id,
            'username'       => $user->username,
            'password'       => $user->password,
            'email'          => $user->mail,
            'emailVerified'  => empty($user->mail_checked) ? 'false' : 'true',
            'creationTime'   => strtotime($user->created_at),
            'lastUpdateTime' => strtotime($user->changed_at),
            'avatarUrl'      => $user->profile_image_url,
            'biography'      => empty($user->biography) ? '' : $user->biography,
            'admin'          => $user->roleId == 100 ? 'true' : 'false',
            'is_hive'        => empty($user->source_id) ? 'false' : 'true',
            'is_active'      => $user->is_active,
            'is_deleted'     => $user->is_deleted
        );

        Zend_Registry::get('logger')->debug(__METHOD__ . ' - $data:' . print_r($data, true));

        return $data;
    }

    protected function getUserData($member_id)
    {
        $modelMember = new Default_Model_Member();

        return $modelMember->find($member_id)->current();
    }

    public function updateMailForUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        return $this->updateUser($member_id);
    }

    public function updateUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        $data = $this->mapUserData($member_id);

        $options = array('bypassEmailCheck' => 'true', 'bypassUsernameCheck' => 'true', 'update' => 'true');

        return $this->id_server->pushHttpUserData($data, $options);
    }

    public function updatePasswordForUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        return $this->updateUser($member_id);
    }

    public function deactivateLoginForUser($member_id)
    {
        if (empty($member_id)) {
            return false;
        }

        return $this->updateUser($member_id);
    }

}