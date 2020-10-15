<?php
namespace App\Controller;

use Tk\Form;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Install extends \Uni\Controller\Install
{


    /**
     * @return \Tk\Uri
     */
    public function getRedirectUrl()
    {
        return \Tk\Uri::create();
    }

    /**
     * @param Form $form
     * @param Form\Event\Iface $event
     * @throws \Exception
     */
    protected function doInstall($form, $event)
    {
        if ($this->getConfig()->isDebug()) {
            $sql = <<<SQL
-- TODO: see if this can be run here??
UPDATE institution t SET t.name = 'Faculty of Veterinary and Agricultural Sciences', t.phone = '(03) 9731 2274', t.email = 'anat-vet@unimelb.edu.au' WHERE t.id = 1;

INSERT INTO user (institution_id, type, username, password ,name_first, name_last, email, active, hash, modified, created)
VALUES
  (1, 'staff', 'mifsudm', MD5(CONCAT('password', MD5('31mifsudm'))), 'Mick', 'Mifsud', 'mifsudm@unimelb.edu.au', 1, MD5('31mifsudm'), NOW(), NOW()),
  (1, 'staff', 'rich', MD5(CONCAT('password', MD5('41rich'))), 'Richard', '', 'richard.ploeg@unimelb.edu.au', 1, MD5('41rich'), NOW(), NOW())
;

INSERT INTO user_permission (user_id, name)
VALUES
    (3, 'perm.manage.site'),
    (3, 'perm.masquerade'),
    (3, 'perm.manage.plugins'),
    (3, 'perm.manage.staff'),
    (4, 'perm.manage.site'),
    (4, 'perm.masquerade'),
    (4, 'perm.manage.plugins'),
    (4, 'perm.manage.staff')
;

DROP TABLE _user_role;
DROP TABLE _user_role_id;
DROP TABLE _user_role_institution;
DROP TABLE _user_role_permission;

SQL;
            try {
                $this->getConfig()->getDb()->exec($sql);
            } catch (\Exception $e) {
                vd($e->__toString());
            }
        }
    }


}