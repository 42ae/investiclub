<?php
/**
 * InvestiClub
 *
 * LICENSE
 *
 * This file may not be duplicated, disclosed or reproduced in whole or in part
 * for any purpose without the express written authorization of InvestiClub.
 *
 * @category	InvestiClub
 * @package		Model
 * @copyright	Copyright (c) 2011-2013 All Rights Reserved
 * @license		http://investiclub.net/license
 */


/**
 * Signup model
 * 
 * @author		Alexandre Esser
 * @category	InvestiClub
 * @package		Model
 * @subpackage	Users
 */
class Model_Users_Signup
{
    
    const ACTIVATE_ACCOUNT_SUCCESS = "Success! Your account is now activate.";
    const ACTIVATE_ACCOUNT_ERROR = "Error! Your account can't be activate, contact an administrator for more information.";
    /**
     * Translate instance
     * @var Zend_Translate_Adapter
     */
    protected $_translate = null;

    /**
     * Array of user's information provided by the SignUp form
     * @var array
     */
    protected $_data = array();

    /**
     * Array of user's settings
     * @var array
     */
    protected $_settings = array();

    /**
     * Class constructor
     *
     * The constructor get the translate adapter from the registry
     * and return an instance of itself.
     * 
     * This method receives an array $data directly linked to
     * the SignUp form. It contains the minimum information required
     * to register a user. It also include a locale variable that
     * will set the default setting values of a new user such as his
     * currency.
     * 
     * @param	array $values
     * @return	Model_Account_Signup
     * @see		Form_Account_Signup
     */
    public function __construct()
    {
    }
    
    public function registerUser($userData)
    {
        $data['first_name'] = $userData['first_name'];
        $data['last_name']  = $userData['last_name'];
        $data['email']      = $userData['email'] ?: null;
        $data['password']   = Ivc_Utils::encryptPassword($userData['password']);
        $data['created_on'] = Zend_Date::now()->toString(Zend_Date::ISO_8601);
        
        $gateway = new Ivc_Model_Users_Gateway();
        return $gateway->createUser($data)->save();
    }

    /**
     * Insert a new registered user into the database
     * 
     * @see		Ivc_Model_Users_Session
     * @return	Ivc_Model_Users_User
     */
    public function signUp($data)
    {
        $user = $this->registerUser($data);
        
        $currency = new Zend_Currency($data['locale']);
        $settings = $user->getSettings();
        $settings->locale = $data['locale'];
        $settings->timezone = Zend_Registry::get('session.l10n')->timezone;
        $settings->currency = $currency->getShortName();
        $settings->save();
        
        $this->_sendActivationLink($user);
        return $user;
    }

    private function _sendActivationLink($user)
    {
        $mail = new Ivc_Mail;
        
        if (APPLICATION_ENV === 'development') {
            // If development server, send to dev@investiclub.net in all cases
            $mail->setRecipient(Zend_Registry::get('config')->email->defaultRecipient);
        } else {
            $mail->setRecipient($user->email);
        }
        $mail->setTemplate(Ivc_Mail::SIGNUP_ACTIVATION);
        $mail->token = Ivc_Utils::getActivationToken($user->email);
        $mail->email = $user->email;
        $mail->firstName = $user->firstName;
        $mail->lastName = $user->lastName;
        $mail->send();
    }
    
    /**
     * Send a confirmation email to a new registered user and generated
     * a token hashed in MD5 according to the user's email and the
     * satic salt stored in the application.
     * 
     * If the mail has been correctly sent, this method returns
     * true, otherwise it returns false.
     * 
     * @see		Ivc_Mail
     * @return	boolean
     */    

    /**
     * Check if either an email and token are correct.
     * If so, this method activate a user account in changing
     * his "active" flag to true in the database.
     * 
     * If the credentials are valid and the account has not been
     * activate so far, then it return true. Otherwise it returns
     * false.
     * 
     * @return	boolean
     */   
    public function activateAccount($email, $token)
    {
        $gateway = new Ivc_Model_Users_Gateway();
        $user = $gateway->fetchByEmail($email, false);
        if ($user !== null AND !$user->last_login) {
            if (!strcmp(Ivc_Utils::getActivationToken($email), $token)) {
                $user->active = true;
                $user->save();
                
                $currency = new Zend_Currency();
                $settings = $user->getSettings();
                $settings->locale = Zend_Registry::get('session.l10n')->locale;
                $settings->timezone = Zend_Registry::get('session.l10n')->timezone;
                $settings->currency = $currency->getShortName();
                $this->getMessages()->push(Ivc_Message::SUCCESS, self::ACTIVATE_ACCOUNT_SUCCESS);
                return;
            }
        }
        $this->getMessages()->push(Ivc_Message::ERROR, self::ACTIVATE_ACCOUNT_ERROR);
    }
}