<?php
namespace App\Db;


use Uni\Db\User;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Permission extends \Uni\Db\Permission
{

    /**
     * TODO: can we use different permissions to allow different actions
     *      on a case??? What roles are available to the stakeholders
     *      (pathologist, accounts, lab, .... ???)
     *
     * Pathologist:
     *
     * @target staff
     */
    const IS_PATHOLOGIST        = 'perm.is.pathologist';



    /**
     * Get all available permissions for a user type
     * If type is null then all available permissions should be returns, excluding the type permissions
     *
     * @param string $type
     * @return array
     */
    public static function getPermissionList($type = '')
    {
        $arr = array();
        switch ($type) {
            case User::TYPE_ADMIN;
            case User::TYPE_CLIENT:
                $arr = array(
                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
                break;
            case User::TYPE_STUDENT:
                $arr = array();
                break;
            default:          // TYPE_STAFF
                $arr = array(
                    'Manage Site Settings' => self::MANAGE_SITE,
                    'Manage Staff Records' => self::MANAGE_STAFF,
                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE
                    //'Course, Subject And Enrollment Settings' => self::MANAGE_SUBJECT,
                    //'Staff Member is a Course Coordinator' => self::IS_COORDINATOR,
                    //'Staff Member is a Lecturer' => self::IS_LECTURER,
                    //'Staff Member is a Student Mentor' => self::IS_MENTOR,

                );
        }
        return $arr;
    }

    /**
     * Return the default permission set for creating new user types.
     *
     * @param string $type
     * @return array
     */
    public static function getDefaultPermissionList($type = '')
    {
        $list = self::getPermissionList($type);
        return $list;
    }
}
