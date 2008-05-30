<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of the PWAK (PHP Web Application Kit) framework.
 *
 * PWAK is a php framework initially developed for the
 * {@link http://onlogistics.googlecode.com Onlogistics} ERP/Supply Chain
 * management web application.
 * It provides components and tools for developers to build complex web
 * applications faster and in a more reliable way.
 *
 * PHP version 5.1.0+
 * 
 * LICENSE: This source file is subject to the MIT license that is available
 * through the world-wide-web at the following URI:
 * http://opensource.org/licenses/mit-license.php
 *
 * @package   PWAK
 * @author    ATEOR dev team <dev@ateor.com>
 * @copyright 2003-2008 ATEOR <contact@ateor.com> 
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   SVN: $Id: Session.php,v 1.7 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class Session {
    // propri�t�s {{{

    /**
     * Instance singleton
     *
     * @var    object $instance
     * @access protected
     */
    protected static $instance = false;

    // }}}
    // Constructeur {{{

    /**
     * Constructeur
     *
     * @access public
     */
    public function __construct() {
        $this->start();
    }

    // }}}
    // Session::singleton() {{{

    /**
     * Retourne un singleton de la classe Session
     *
     * @access public
     * @static
     * @param  boolean $saveURL
     * @return object Session
     */
    public static function singleton() {
        if (!self::$instance) {
            self::$instance = new Session();
        }
        return self::$instance;
    }

    // }}}
    // Session::start() {{{

    /**
     * D�marre la session et purge les variables
     *
     * @access public
     * @return void
     */
    public function start() {
        // pour �viter les conflits de session entre differents projects 
        // h�berg�s sur un m�me "hostname"
        session_name(md5(PROJECT_ROOT));
        if (defined('SKIP_CONNECTION')) {
            // requ�te "sp�ciale" (ajax, xmlrpc etc...) on utilise pas le
            // syst�me de timeout
            if (isset($_REQUEST['sid'])) {
                session_id($_REQUEST['sid']);
            } elseif (isset($_COOKIE['PHPSESSID'])) {
                session_id($_COOKIE['PHPSESSID']);
            }
            session_start();
            // et il ne faut pas non plus d�cr�menter vars_timeout
            if(defined('IGNORE_SESSION_TIMEOUT')) {
                return;
            }
        } else {
            // requ�te http "normale" (browser)
            session_start();
            if(defined('IGNORE_SESSION_TIMEOUT')) {
                return;
            }
            // timeout de la session au bout de x secondes d'inactivit�
            if(isset($_SESSION['session_timeout']) && SESSION_TIMEOUT > 0 &&
               time() - $_SESSION['session_timeout'] > SESSION_TIMEOUT) {
                session_destroy();
                session_start();
            }
            $_SESSION['session_timeout'] = time();
        }
        // timeout de pages pour les variables enregistr�es avec la m�thode
        // Session::register().
        if (isset($_SESSION['vars_timeout'])) {
            foreach ($_SESSION['vars_timeout'] as $name=>$value) {
                if ($value == 0) {
                    $this->unregister($name);
                } else {
                    $_SESSION['vars_timeout'][$name]--;
                }
            }
        }
    }

    // }}}
    // Session::register() {{{

    /**
     * Enregistre une variable $name en session pour une dur�e de $timeout
     * pages.
     *
     * @access public
     * @param  string $name
     * @param  mixed  $value
     * @param  integer $timeout
     * @return void
     */
    public function register($name, $value, $timeout = 1) {
        // enregistrement de la variable
        $_SESSION[$name] = $value;
        // Importation dans le compteur de variable
        if (!isset($_SESSION['vars_timeout'])) {
            $_SESSION['vars_timeout'] = array();
        }
        // enregistrement du compteur de la variable
        $_SESSION['vars_timeout'][$name] = $timeout;
    }

    // }}}
    // Session::unregister() {{{

    /**
     * Supprime la variable $name de la session en cours.
     *
     * @access public
     * @param  string $name
     * @return void
     */
    public function unregister($name) {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
            unset($_SESSION['vars_timeout'][$name]);
        }
    }

    // }}}
    // Session::prolong() {{{

    /**
     * Prolonge la dur�e de vie de la variable $name pour le nombre de page 
     * indiqu� par $timeout.
     *
     *
     * @access public
     * @param  string $name
     * @param  integer $timeout
     * @return void
     */
    public function prolong($name, $timeout = 1) {
        if (isset($_SESSION['vars_timeout'][$name])) {
            $_SESSION['vars_timeout'][$name] = $timeout;
        }
    }

    // }}}
}

?>
