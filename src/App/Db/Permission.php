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
     *  - Gets disposal reminder emails
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
     * If a staff member has the full edit permission
     * then when a case is completed they still have full edit control.
     * Other users are blocked from editing a case when set to completed
     *
     * @target staff
     */
    const CASE_FULL_EDIT            = 'perm.case.full.edit';


    /**
     * @param string $type (optional) If set returns only the permissions for that user type otherwise returns all permissions
     * @return array|string[]
     */
    public function getAvailablePermissionList($type = '')
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
                    //'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Manage Staff Records' => self::MANAGE_STAFF,
                    'Can Masquerade' => self::CAN_MASQUERADE,
                    'Is Pathologist' => self::IS_PATHOLOGIST,
                    'Is Technician' => self::IS_TECHNICIAN,
                    'Case Administration' => self::CASE_ADMIN,
                    'Case Always Edit' => self::CASE_FULL_EDIT
                );
        }
        return $arr;
    }

    public function getPermissionDescriptions(): array
    {
        return [
            self::MANAGE_SITE => 'Can manage site settings and manage site configuration (Notes, Cms content)',
            self::MANAGE_PLUGINS => 'Can manage site plugins (deprecated)',
            self::CAN_MASQUERADE => 'Can masquerade as lower permission users to view their data',
            self::MANAGE_STAFF => 'Can create/update other staff user accounts',
            self::MANAGE_SUBJECT => 'Can manage Subject settings, enrollment',
            self::IS_COORDINATOR => 'Is the coordinator of a Course, access/emails/notifications of associated Courses, Subjects and Students ',
            self::IS_LECTURER => 'Is a lecturer of a subject, access/email/notifications of associated Subjects, Students',
            self::IS_MENTOR => 'Is a mentor of a student, restricted access to student/site information',
            self::IS_PATHOLOGIST => 'Can be selected as a pathologist of a case',
            self::IS_TECHNICIAN => 'Receive disposal reminder emails',
            self::CASE_ADMIN => 'Change the status of a case after a case is set to completed',
            self::CASE_FULL_EDIT => 'Edit a case after a case is set to completed',
        ];
    }

    /**
     * Return the default user permission when creating a user
     *
     * @param string $type (optional) If set returns only the permissions for that user type otherwise returns all permissions
     * @return array|string[]
     */
    public function getDefaultUserPermissions($type = '')
    {
        return array(); // All permissions must be set by a self::MANAGE_STAFF user
    }
}
