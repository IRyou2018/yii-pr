<?php

namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    const LECTURER = 0;
    const STUDENT = 1;

    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * Validates via LDAP
     */
    public function validateLDAP()
    {

        $userInfo = [];

        if (!$this->hasErrors()) {

            $ldapserver = 'computing.dundee.ac.uk';
            $ldapuser = $this->username;  
            $ldappass = $this->password;
            $studenttree = "OU=students,DC=computing,DC=dundee,DC=ac,DC=uk";
            $lecturertree = "OU=staff,DC=computing,DC=dundee,DC=ac,DC=uk";

            $ldapconn = @ldap_connect($ldapserver);

            $flag = true;

            if ($ldapconn) {

                $ldapbind = @ldap_bind($ldapconn, $ldapuser, $ldappass);

                if ($ldapbind) {

                    $dataStudent = $this->getLDAPUser($studenttree,$ldapuser,$ldapconn);

                    if ($dataStudent["count"] != 0) {

                        $userInfo = array(
                            'email' => $dataStudent[0]["mail"][0],
                            'firstname' => $dataStudent[0]["givenname"][0],
                            'lastname' => $dataStudent[0]["sn"][0],
                            'matric' =>  strpos($dataStudent[0]["employeeid"][0], "/") ? substr($dataStudent[0]["employeeid"][0], 0, strpos($dataStudent[0]["employeeid"][0], "/")) : $dataStudent[0]["employeeid"][0],
                            'type' => self::STUDENT
                          );
                        ldap_close($ldapconn);

                    } else {

                        $dataLecturer = $this->getLDAPUser($lecturertree,$ldapuser,$ldapconn);

                        if ($dataLecturer["count"] != 0) {

                            $userInfo = array(
                                'email' => $dataLecturer[0]["mail"][0],
                                'firstname' => $dataLecturer[0]["givenname"][0],
                                'lastname' => $dataLecturer[0]["sn"][0],
                                'matric' =>  strpos($dataLecturer[0]["employeeid"][0], "/") ? substr($dataLecturer[0]["employeeid"][0], 0, strpos($dataLecturer[0]["employeeid"][0], "/")) : $dataLecturer[0]["employeeid"][0],
                                'type' => self::LECTURER
                              );
                            ldap_close($ldapconn);
                        } else {
                            ldap_close($ldapconn);
                            $flag = false;
                        }
                    }
                } else {
                    ldap_close($ldapconn);
                    $flag = false;
                }
            } else {
                $flag = false;
            }

            if ($flag) {
            } else {
                $this->addError('username', 'Incorrect username or password.');
                $this->addError('password', 'Incorrect username or password.');
            }
        }

        return $userInfo;
    }

    /**
     * Gets the information of the user and sets it up in the application database.
     * @param  [string] $ldaptree [ldap tree]
     * @param  [string] $ldapuser [ldap user type]
     * @param  [string] $ldapconn [ldap connection]
     * @return [Array] [founded data]
     */
    private function getLDAPUser($ldaptree, $ldapuser, $ldapconn)
    {
        $search = @ldap_search($ldapconn, $ldaptree, "(|(cn=$ldapuser))");
        $data = ldap_get_entries($ldapconn, $search);

        return $data;
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $userInfo = $this->validateLDAP();

            if (!empty($userInfo)) {
                return Yii::$app->user->login($this->getUser($userInfo), $this->rememberMe ? 3600 * 24 * 30 : 0);
            }
        }
        
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser($userInfo)
    {
        $user = User::findByMatric($userInfo['matric']);
        
        if ($user === null) {
            $modelUser = new User();
            $modelUser->first_name = $userInfo['firstname'];
            $modelUser->last_name = $userInfo['lastname'];
            $modelUser->matric_number = $userInfo['matric'];
            $modelUser->email = $userInfo['email'];
            $modelUser->type = self::STUDENT;
            $modelUser->generateAuthKey();

            if($modelUser->save()) {
                $this->_user = $modelUser;
            }
        } else {
            $this->_user = $user;
            
        }
        return $this->_user;
    }
}
