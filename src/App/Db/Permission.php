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
     * Pathologist:
     *  - Can edit all non completed cases
     *
     * @target staff
     */
    const IS_PATHOLOGIST        = 'perm.is.pathologist';

    /**
     * Technician:
     *  - Can edit all non completed cases
     *  - Gets disposal reminder emails
     *
     * @target staff
     */
    const IS_TECHNICIAN        = 'perm.is.technician';

    /**
     * Technician:
     *  - Can edit all non completed cases
     *
     * @target staff
     */
    const IS_HISTOLOGIST        = 'perm.is.histologist';

    /**
     * Permission to suppress any email reminders
     * Used for external pathologists that do not set the report to complete
     *
     * @target staff
     */
    const IS_EXTERNAL       = 'perm.is.external';

    /**
     * If a staff member has the full edit permission
     * then when a case is completed they still have full edit control.
     * Other users are blocked from editing a case when set to completed
     *
     * @target staff
     */
    const CASE_FULL_EDIT            = 'perm.case.full.edit';

    /**
     * If a staff member has the full edit permission
     * then when a case is completed they still have full edit control.
     * Other users are blocked from editing a case when set to completed
     *
     * @target staff
     */
    const CAN_REVIEW_CASE            = 'perm.case.can.review';


    /**
     * @param string $type (optional) If set returns only the permissions for that user type otherwise returns all permissions
     * @return array|string[]
     */
    public function getAvailablePermissionList($type = '')
    {
        $arr = [];
        switch ($type) {
            case User::TYPE_ADMIN;
            case User::TYPE_CLIENT:
                $arr = array(
                    'Manage Site Plugins' => self::MANAGE_PLUGINS,
                    'Can Masquerade' => self::CAN_MASQUERADE
                );
                break;
            case User::TYPE_STUDENT:
                break;
            default:          // TYPE_STAFF
                $arr = array(
                    'Manage Site Settings' => self::MANAGE_SITE,
                    'Manage Staff Records' => self::MANAGE_STAFF,
                    'Can Masquerade' => self::CAN_MASQUERADE,
                    'Is Pathologist' => self::IS_PATHOLOGIST,
                    'Is Technician' => self::IS_TECHNICIAN,
                    'Is Histologist' => self::IS_HISTOLOGIST,
                    'Is External' => self::IS_EXTERNAL,
                    'Review Cases' => self::CAN_REVIEW_CASE,
                    'Case Always Edit' => self::CASE_FULL_EDIT
                );
        }
        return $arr;
    }

    public function getPermissionDescriptions(): array
    {
        return [
            self::MANAGE_SITE => 'Can manage site settings and manage site configuration (Notes, Content Pages)',
            self::MANAGE_STAFF => 'Can create/update other staff user accounts',
            //self::MANAGE_PLUGINS => 'Can manage site plugins (deprecated)',
            self::CAN_MASQUERADE => 'Can masquerade as lower permission users',
            //self::MANAGE_SUBJECT => 'Can manage Subject settings, enrollment',
            //self::IS_COORDINATOR => 'Is the coordinator of a Course, access/emails/notifications of associated Courses, Subjects and Students ',
            //self::IS_LECTURER => 'Is a lecturer of a subject, access/email/notifications of associated Subjects, Students',
            //self::IS_MENTOR => 'Is a mentor of a student, restricted access to student/site information',
            self::IS_PATHOLOGIST => 'Can view/edit all Cases and report fields, send report emails',
            self::IS_TECHNICIAN => 'Can view/edit all Cases, sent report email, cannot change report status of cases, receives disposal reminders',
            self::IS_HISTOLOGIST => 'Can view/edit all Cases, sent report email, cannot change report status of cases',
            self::IS_EXTERNAL => 'No reminders are sent to external users',
            self::CAN_REVIEW_CASE => 'Pathologists will be able to set this user as the Case reviewer',
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
