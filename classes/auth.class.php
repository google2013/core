<?php

/**
 * 验证类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Auth
{
    /**
     * 数据库类型
     *
     * @var string
     */
    const DRIVER_DATABASE = 'Database';

    /**
     * 文件类型
     *
     * @var string
     */
    const DRIVER_FILE = 'File';

    /**
     * 当前配置
     *
     * @var array
     */
    protected $config;

    protected $config_name;

    /**
     * @var Auth
     */
    protected static $instance;

    protected static $user_info = array();

    /**
     * @return Auth
     */
    public static function instance($config_name='default')
    {
        if (null===Auth::$instance)
        {
            // Create a new instance
            Auth::$instance = new Auth($config_name);
        }
        return Auth::$instance;
    }

    public function __construct($config_name='default')
    {
        $this->config_name = $config_name;
        $this->config = Core::config('auth.'.$config_name);
    }

    /**
     * 检查用户名密码
     *
     * @param string $username
     * @param string $password
     * @return Member
     * @throws Exception
     */
    public function check_user($username, $password)
    {
        $member = $this->get_member_by_username($username);

        if (!$member)
        {
            throw new Exception(__('The user does not exist'));
        }

        if ( $member->check_password($password) )
        {
            return $member;
        }
        else
        {
            throw new Exception(__('Enter the wrong password'));
        }
    }

    /**
     * 根据用户名获取用户
     *
     * @param string $username
     * @return 用户对象，不存在则返回false
     */
    public function get_member_by_username($username)
    {
        if (!isset(Auth::$user_info[$this->config_name][$username]))
        {
            if ( $this->config['driver']==Auth::DRIVER_DATABASE )
            {
                # 数据库类型
                $tables = $this->config['tablename'];
                $user_field = $this->config['username_field'];
                $password_field = $this->config['password_field'];
                $data = Database::instance($this->config['database'])
                ->from($tables)
                ->where($user_field,$username)
                ->limit(1)
                ->get()
                ->current();
            }
            elseif ( $this->config['driver']==Auth::DRIVER_FILE )
            {
                //TODO 文件格式
            }

            if ($data)
            {
                $member_obj = $this->config['member_object_name']?$this->config['member_object_name']:'Member';
                Auth::$user_info[$this->config_name][$username] = new $member_obj($data);
            }
            else
            {
                Auth::$user_info[$this->config_name][$username] = False;
            }
        }

        return Auth::$user_info[$this->config_name][$username];
    }
}