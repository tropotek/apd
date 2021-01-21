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
     *
     * Pathologist:
     *  - Will be used in future
     *
     * @target staff
     */
    const IS_PATHOLOGIST        = 'perm.is.pathologist';

    /**
     *
     *
     * Technician:
     *  - Get disposal reminder emails
     *
     * @target staff
     */
    const IS_TECHNICIAN        = 'perm.is.technician';

    /**
     * If a staff member is a case admin they have permission
     * to change the status of a case at any time
     * Other staff cannot change the status once a case is completed
     *
     * @target staff
     */
    const CASE_ADMIN            = 'perm.manage.case';



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
                    //'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE,
                    //'Is Pathologist' => self::IS_PATHOLOGIST,
                    'Is Technician' => self::IS_TECHNICIAN,
                    'Case Administration' => self::CASE_ADMIN
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
        return array(); // All permissions must be set by a self::MANAGE_STAFF user
//        $list = self::getPermissionList($type);
//        unset($list['Case Administration']); // must be set by admin user
//        unset($list['Is Pathologist']); // must be set by admin user
//        unset($list['Is Technician']); // must be set by admin user
//        return $list;
    }
}
