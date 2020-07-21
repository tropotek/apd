<?php
namespace App\Listener;


use Bs\Db\User;
use Tk\Event\AuthEvent;
use Tk\Auth\AuthEvents;
use Uni\Db\Permission;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AuthHandler extends \Bs\Listener\AuthHandler
{

    /**
     * @param \Tk\Event\AuthEvent $event
     * @return null|void
     * @throws \Exception
     */
    public function onLoginProcess(\Tk\Event\AuthEvent $event)
    {
        $config = $this->getConfig();
        if ($config->getMasqueradeHandler()->isMasquerading()) {
            $config->getMasqueradeHandler()->masqueradeClear();
        }

        if ($event->getAdapter() instanceof \Tk\Auth\Adapter\Ldap) {
            /** @var \Tk\Auth\Adapter\Ldap $adapter */
            $adapter = $event->getAdapter();
            $config = \App\Config::getInstance();
            // Find user data from ldap connection
            $filter = substr($adapter->getBaseDn(), 0, strpos($adapter->getBaseDn(), ','));
            if ($filter) {
                $ldapData = $adapter->ldapSearch($filter);
                if ($ldapData) {
                    $email = '';
                    if (!empty($ldapData[0]['mail'][0])) $email = trim($ldapData[0]['mail'][0]);   // Email format = firstname.lastname@unimelb
                    $uid = '';
                    if (!empty($ldapData[0]['auedupersonid'][0])) $uid = trim($ldapData[0]['auedupersonid'][0]);
                    $username = $adapter->get('username');

                    /* @var \Uni\Db\User $user */
                    $user = $config->getUserMapper()->findByUsername($adapter->get('username'), $config->getInstitutionId());
                    if (!$user) {   // Error out if no user
                        $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID,
                                $adapter->get('username'), 'Invalid username. Please contact your administrator to setup an account.'));
                        return;
                    }

//                    if (!$user) { // Create a user record if none exists
//                        if (!$config->get('auth.ldap.auto.account')) {
//                            $msg = sprintf('Please contact your site administrator to enable your user account. Please provide the following details' .
//                                "\nusername: %s\nUID: %s\nEmail: %s", $adapter->get('username'), $uid, $email);
//                            $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, $adapter->get('username'), $msg));
//                        }
//
//                        $type = 'student';
//                        if (preg_match('/(staff|student)/', strtolower($ldapData[0]['auedupersontype'][0]), $reg)) {
//                            if ($reg[1] == 'staff') $type = 'staff';
//                        }
//
//                        if ($type == 'student') {
//                            // To check if a user is pre-enrolled get an array of uid and emails for a user
//                            $isPreEnrolled = $config->getSubjectMapper()->isPreEnrolled($config->getInstitutionId(),
//                                array_merge($ldapData[0]['mail'], $ldapData[0]['mailalternateaddress']),
//                                $ldapData[0]['auedupersonid'][0]
//                            );
//
//                            if (!$isPreEnrolled) {      // Only create users accounts for enrolled students
//                                $msg = sprintf('We cannot find any enrolled subjects. Please contact your coordinator.' .
//                                    "\nusername: %s\nUID: %s\nEmail: %s", $adapter->get('username'), $uid, $email);
//                                $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID, $adapter->get('username'), $msg));
//                                return;
//                            }
//
//                            $userData = array(
//                                'authType' => 'ldap',
//                                'institutionId' => $config->getInstitutionId(),
//                                'username' => $adapter->get('username'),
//                                'type' => $type,
//                                'active' => true,
//                                'email' => $email,
//                                'name' => $ldapData[0]['displayname'][0],
//                                'uid' => $ldapData[0]['auedupersonid'][0],
//                                'ldapData' => $ldapData
//                            );
//                            $user = $config->createUser();
//                            $config->getUserMapper()->mapForm($userData, $user);
//                            $error = $user->validate();
//                            if (count($error)) {
//                                try {
//                                    $user->setNewPassword($adapter->get('password'));
//                                } catch (\Exception $e) {
//                                    \Tk\Log::info($e->__toString());
//                                }
//                            }
//                        } else {
//                            $msg = sprintf('Staff members can contact the site administrator to request access');
//                            $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID,
//                                $adapter->get('username'), $msg));
//                            return;
//                        }
//                    }

                    if ($user && $user->isActive()) {
                        if (!$user->getUid() && !empty($ldapData[0]['auedupersonid'][0]))
                            $user->setUid($ldapData[0]['auedupersonid'][0]);
                        if (!$user->getName() && !empty($ldapData[0]['displayname'][0]))
                            $user->setName($ldapData[0]['displayname'][0]);
                        // TODO: update this to if !$user->email later once all emails are changed over
                        if ($email)
                            $user->setName($email);
                        $user->setNewPassword($adapter->get('password'));
                        $user->save();
                        $user->addPermission(\Uni\Db\Permission::getDefaultPermissionList($user->getType()));

                        if (method_exists($user, 'getData')) {
                            $data = $user->getData();
                            $data->set('ldap.last.login', json_encode($ldapData));
                            if (!empty($ldapData[0]['ou'][0]))
                                $data->set('faculty', $ldapData[0]['ou'][0]);
                            $data->save();
                        }

                        $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $config->getUserIdentity($user)));
                        //$config->getSession()->set('auth.password.access', false);
                    }
                }
            }
        }

        // LTI Authentication
        if ($event->getAdapter() instanceof \Lti\Auth\LtiAdapter) {
            $config = \Uni\Config::getInstance();

            /** @var \Lti\Auth\LtiAdapter $adapter */
            $adapter = $event->getAdapter();
            $userData = $adapter->get('userData');
            $subjectData = $adapter->get('subjectData');
            $ltiData = $adapter->getLaunchData();

            // Setup/Find User and log them in
            $user = $config->getUserMapper()->findByUsername($adapter->get('username'), $adapter->getInstitution()->getId());
            if (!$user)
                $user = $config->getUserMapper()->findByEmail($userData['email'], $adapter->getInstitution()->getId());
            // Error out if no user
//            if (!$user) {
//                $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID,
//                    $userData['username'], 'Invalid username. Please contact your administrator to setup an account.'));
//                return;
//            }
            // Create the new user account
            if (!$user) {
                $user = $config->createUser();
                $config->getUserMapper()->mapForm($userData, $user);
                $user->save();
                $user->addPermission(\Uni\Db\Permission::getDefaultPermissionList($user->getType()));
                $adapter->set('user', $user);
            }
            // Update user details from login
            if (!$user->getEmail())
                $user->setEmail($userData['email']);
            if (!$user->getName())
                $user->setName($userData['name']);
            if (!$user->getImage() && !empty($userData['image']))
                $user->setImage($userData['image']);

            // Find Subject
            $subject = $config->getSubjectMapper()->findFiltered(
                array('code' => $subjectData['subjectCode'], 'institutionId' => $adapter->getInstitution()->getId())
            )->current();
            if (!empty($subjectData['subjectId'])) {
                $s = $config->getSubjectMapper()->find($subjectData['subjectId']);
                if ($s) $subject = $s;
            }

            // Create a new subject
            if (!$subject) {
                if (!$user->isStaff()) {        // Only auto create subject for staff users
                    $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID,
                        $userData['username'], 'Subject [' . $subjectData['subjectCode'] . '] not available, Please contact the subject coordinator.'));
                    return;
                }

                // Find/Create course
                $course = $config->getCourseMapper()->findFiltered(
                    array('code' => $subjectData['courseCode'], 'institutionId' => $adapter->getInstitution()->getId())
                )->current();
                if (!$course) {
                    // Create a new Course and Subject here if needed
                    $course = $config->createCourse();
                    $course->setInstitutionId($adapter->getInstitution()->getId());
                    $course->setName($subjectData['name']);
                    $course->setEmail($subjectData['email']);
                    $course->setCode($subjectData['courseCode']);
                    if ($user->hasPermission(Permission::IS_COORDINATOR))
                        $course->setCoordinatorId($user->getId());
                    $course->save();
                    $subjectData['isNewCourse'] = true;
                }

                // Subject
                $subject = $config->createSubject();
                $config->getSubjectMapper()->mapForm($subjectData, $subject);
                if ($course)
                    $subject->setCourseId($course->getId());
                $subject->save();
                $subjectData['isNewSubject'] = true;
                $adapter->set('subject', $subject);
            }


            $config->getSession()->set('lti.subjectId', $subject->getId());   // Limit the dashboard to one subject for LTI logins
            $event->setRedirect(\Uni\Uri::createSubjectUrl('/index.html', $subject, $user));

            // (optional) to check the pre-enrollment, if not available fail authentication
//            $isPreEnrolled = $config->getSubjectMapper()->isPreEnrolled($adapter->getInstitution()->getId(), array($user->getEmail()) );
//            if (!$isPreEnrolled) {  // Only create users accounts for enrolled students
//                $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::FAILURE_CREDENTIAL_INVALID,
//                    $userData['username'], 'You are not enrolled. Please contact your administrator to setup your account.'));
//                return;
//            }

            // Add user to the subject if not already enrolled
            if (!$config->getSubjectMapper()->hasUser($subject->getId(), $user->getId())) {
                if ($user->isStudent())
                    $config->getSubjectMapper()->addUser($subject->getId(), $user->getId());
                if ($user->isStaff())
                    $config->getCourseMapper()->addUser($subject->getCourseId(), $user->getId());
            }

            $user->save();
            if ($ltiData && method_exists($user, 'getData')) {
                $data = $user->getData();
                $data->set('lti.last.login', json_encode($ltiData));
                $data->save();
            }


            $config->getSession()->set('auth.password.access', false);
            $event->setResult(new \Tk\Auth\Result(\Tk\Auth\Result::SUCCESS, $config->getUserIdentity($user)));
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @throws \Exception
     */
    public function onRequest( $event)
    {
        // if a user is in the session add them to the global config
        // Only the identity details should be in the auth session not the full user object, to save space and be secure.
        $config = \Uni\Config::getInstance();
        $auth = $config->getAuth();
        $user = null;                       // public user
        if ($auth->getIdentity()) {         // Check if user is logged in
            /** @var \Uni\Db\User $user */
            $user = $config->getUserMapper()->findByAuthIdentity($auth->getIdentity());
            if ($user && $user->isActive()) {
                $config->setAuthUser($user);            // We set the user here
            }
        }

        // ---------------- deprecated  ---------------------
        // The following is deprecated in preference of the validatePageAccess() method

        $type = $event->getRequest()->attributes->get('role');
        if (!$type || empty($type)) return;

        if (!$user || $user->isGuest()) {
            if ($event->getRequest()->getTkUri()->getRelativePath() != '/login.html') {
                \Tk\Uri::create('/login.html')->redirect();
            } else {
                \Tk\Alert::addWarning('1001: You do not have access to the requested page.');
                $config->getUserHomeUrl($user)->redirect();
            }
        } else {
            if (!$user->hasType($type)) {
                \Tk\Alert::addWarning('1002: You do not have access to the requested page.');
                $config->getUserHomeUrl($user)->redirect();
            }
        }
        //-----------------------------------------------------

    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogin(AuthEvent $event)
    {
        $config = \Uni\Config::getInstance();
        $auth = $config->getAuth();

        if ($config->getMasqueradeHandler()->isMasquerading()) {
            $config->getMasqueradeHandler()->masqueradeClear();
        }

        $adapter = $config->getAuthDbTableAdapter($event->all());
        $result = $auth->authenticate($adapter);


        $event->setResult($result);
        $event->set('auth.password.access', true);   // Can modify their own password
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function updateUser(AuthEvent $event)
    {
        $config = \Uni\Config::getInstance();
        parent::updateUser($event);
        if ($config->getMasqueradeHandler()->isMasquerading()) return;
        $user = $config->getAuthUser();
        if ($user) {
            if (property_exists($user, 'sessionId') && $user->sessionId != $config->getSession()->getId()) {
                $user->sessionId = $config->getSession()->getId();
            }
            $user->save();
        }
    }

    /**
     * @param AuthEvent $event
     * @throws \Exception
     */
    public function onLogout(AuthEvent $event)
    {
        $config = \Uni\Config::getInstance();
        $auth = $config->getAuth();
        /** @var \Uni\Db\User $user */
        $user = $config->getAuthUser();

        if (!$event->getRedirect()) {
            $url = \Tk\Uri::create('/');
            if ($user && !$user->isClient() && !$user->isAdmin() && $user->getInstitution()) {
                $url = \Uni\Uri::createInstitutionUrl('/login.html', $user->getInstitution());
            }
            $event->setRedirect($url);
        }

        if ($user && $user->getId() && property_exists($user, 'sessionId')) {
            $user->sessionId = '';
            $user->save();
        }

        $config->unsetSubject();
        $config->getSession()->remove('lti.subjectId'); // Remove limit the dashboard to one subject for LTI logins
        $config->getSession()->remove('auth.password.access');
        $auth->clearIdentity();

        if (!$config->getMasqueradeHandler()->isMasquerading()) {
            \Tk\Log::warning('Destroying Session');
            $config->getSession()->destroy();
        };
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array_merge(
            array(AuthEvents::LOGIN_PROCESS => 'onLoginProcess'),
            parent::getSubscribedEvents()
        );
    }


}