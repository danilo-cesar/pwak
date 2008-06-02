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
 * @version   SVN: $Id$
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

if (!defined('PREFERENCES_TABLE_NAME')) {
    define('PREFERENCES_TABLE_NAME', 'FW_Preferences');
}

// }}}

/**
 * Classe de gestion des pr�f�rences de l'application.
 *
 * Cette classe permet de d�finir un nombre illimit� de Preferences pour une
 * application tout en g�rant le type de donn�e, les types g�r�s sont:
 * - string,
 * - integer,
 * - double (aka float),
 * - array (les tableaux sont stock�s serialis�s et encod�s en base64).
 *
 * L'interface est relativement simple, voici un exemple complet:
 * <code>
 * // ajoute (ou modifie) une pr�f�rence de type float
 * Preferences::set('tva_rate', 18.6);
 * // ajoute (ou modifie) une pr�f�rence de type array
 * Preferences::set('colors', array('blue', 'green', 'red'));
 *
 * // r�cup�rer les preferences
 * echo Preference::get('tva_rate');   // affiche 18.6
 * print_r(Preference::get('colors')); // affiche array('blue', 'green', 'red')
 *
 * // supprimer une pr�f�rence, ne sera supprim�e que lors de l'appel � save()
 * Preferences::delete('tva_rate');
 *
 * // sauvegarder les pr�f�rences
 * Preferences::save();
 * </code>
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package    Framework
 * @copyright  2002-2006 ATEOR - All rights reserved
 * @since      version 2.0.1
 * @todo       R�fl�chir � comment lier �a avec les utilisateurs, mais cela
 *             demande d'avoir d�j� r�fl�chi � ce qu'on va faire pour la
 *             gestion des utilisateurs au sein du framework.
 * @todo       V�rifier la gestion des erreurs, s'assurer que toutes les
 *             �ventuelles erreurs sont bien remont�es.
 */
class Preferences
{
    // properties {{{

    /**
     * Tableau des pr�f�rences
     *
     * @var    array $prefs
     * @access protected
     */
    protected static $prefs = array();

    /**
     * Tableau des pr�f�rences qui doivent �tre supprim�es en bdd.
     *
     * @var    array $deletedPrefs
     * @access protected
     */
    protected static $deletedPrefs = array();

    /**
     * Mapping des types et de la colonne de la table correspondante
     *
     * @var    array $typeMap
     * @access protected
     */
    protected static $typeMap = array(
        'string'  => 'string_value',
        'boolean' => 'bool_value',
        'integer' => 'int_value',
        'double'  => 'float_value',
        'array'   => 'array_value',
        'text'    => 'text_value'
    );

    /**
     * Flag permettant de savoir si les pr�f�rences ont �t� charg�es.
     *
     * @var    bool  $preferencesLoaded
     * @access protected
     */
    protected static $preferencesLoaded = false;

    // }}}
    // Preferences::set() {{{

    /**
     * D�fini une Preference, attention si une pr�f�rence de ce nom existe d�j�
     * elle est modifi�e avec la nouvelle valeur.
     *
     * @static
     * @access public
     * @param  string $name
     * @param  string $value
     * @return void
     */
    public static function set($name, $value) {
        self::load();
        $update = isset(self::$prefs[$name]);
        $type = gettype($value);
        if ($type == 'object') {
            trigger_error(
                'Unsupported type "' . $type . '" for key "' . $name . '"',
                E_USER_WARNING
            );
            return;
        }
        if ($type == 'array') {
            // cas particulier des tableaux, il faut passer un tableau
            // (�videmment) qui ne contienne pas d'objets
            $func = create_function('$v', 'return is_object($v);');
            if (!is_array($value) || count(array_filter($value, $func)) > 0) {
                trigger_error(
                    'Wrong value type for an array preference',
                    E_USER_WARNING
                );
                return;
            }
            $value = base64_encode(serialize($value));
        }
        if ($type == 'boolean') {
            $value = $value?'1':'0';
        }
        if ($type == 'string' && strlen($value) > 255) {
            $type = 'text';
        }
        self::$prefs[$name] = array($type, $value, $update, true);
    }

    // }}}
    // Preferences::get() {{{

    /**
     * R�cup�re une Preference.
     * Si default est renseign� et que la Preference n'existe pas c'est cette
     * valeur qui est retourn�e.
     *
     * @static
     * @access public
     * @param  string $name
     * @param  mixed  $default
     * @return mixed integer, boolean, double, string or array
     */
    public static function get($name, $default = null) {
        self::load();
        if (!isset(self::$prefs[$name])) {
            return $default;
        }
        $value = self::$prefs[$name][1];
        if (self::$prefs[$name][0] == 'array') {
            // cas particulier des tableaux, il faut deserializer les donn�es
            $value = unserialize(base64_decode($value));
        }
        return $value;
    }

    // }}}
    // Preferences::delete() {{{

    /**
     * Supprime une Preference.
     * Note: la pr�f�rences n'est supprim�e en base de donn�es que lors de
     * l'appel � la m�thode save().
     *
     * @static
     * @access public
     * @param  string $name
     * @return void
     */
    public static function delete($name) {
        self::load();
        if (!isset(self::$prefs[$name])) {
            return;
        }
        if (!in_array($name, self::$deletedPrefs)) {
            self::$deletedPrefs[] = $name;
        }
        unset(self::$prefs[$name]);
    }

    // }}}
    // Preferences::save() {{{

    /**
     * Sauvegarde les pr�f�rences en base de donn�es et supprime r�ellement
     * celles qui doivent �tre supprim�es (cad celles qui ont �t� supprim�es
     * avec la m�thode delete()).
     *
     * @static
     * @access public
     * @return void
     */
    public static function save() {
        $insertSQL = "INSERT INTO " . PREFERENCES_TABLE_NAME
                   . " (dbid, name, type, %s) VALUES (%s, %s, '%s', %s)";
        $updateSQL = "UPDATE " . PREFERENCES_TABLE_NAME
                   . " SET type='%s', %s=%s WHERE name=%s%s"; // AND dbid%s
        $deleteSQL = "DELETE FROM " . PREFERENCES_TABLE_NAME
                   . " WHERE name='%s'%s"; //  AND dbid%s
        $dbid = defined('DATABASE_ID')?DATABASE_ID:'NULL';
        $dbidstr = ($dbid == 'NULL')?'':' AND dbid='.$dbid;

        foreach (self::$prefs as $name=>$data) {
            list($type, $value, $update, $modified) = $data;
            $name  = Database::connection()->qstr($name);
            $value = Database::connection()->qstr($value);
            if (!$modified) {
                continue;
            }
            $col = self::$typeMap[$type];
            if ($update) {
                $sql = sprintf($updateSQL, $type, $col, $value, $name, $dbidstr);
            } else {
                $sql = sprintf($insertSQL, $col, $dbid, $name, $type, $value);
            }
            Database::connection()->execute($sql);
        }
        // supprime les pr�f�rences qui doivent l'�tre
        while ($name = array_shift(self::$deletedPrefs)) {
            Database::connection()->execute(sprintf($deleteSQL, $name, $dbidstr));
        }
    }

    // }}}
    // Preferences::load() {{{

    /**
     * Charge les pr�f�rences � partir de la base de donn�es si ce n'est pas
     * d�j� fait.
     *
     * @static
     * @access protected
     * @return void
     */
    protected static function load() {
        if (self::$preferencesLoaded) {
            return;
        }
        $sql = 'SELECT * FROM FW_Preferences';
        if (defined('DATABASE_ID') && !Object::isPublicEntity('FW_Preferences')) {
            $sql .= ' WHERE dbid IS NULL OR dbid=' . DATABASE_ID;
        }
        $rs = Database::connection()->execute($sql);
        if ($rs) {
            while (!$rs->EOF) {
                $type = $rs->fields['type'];
                $name = $rs->fields['name'];
                $col  = self::$typeMap[$type];
                $value = $rs->fields[$col];
                self::$prefs[$name] = array($type, $value, true, false);
                $rs->moveNext();
            }
            $rs->close();
        }
        self::$preferencesLoaded = true;
    }

    // }}}
    // Preferences::getAll() {{{
    
    /**
     * R�cup�re les Preferences.
     *
     * @static
     * @access public
     * @return mixed
     */
    public static function getAll() {
        self::load();
        $return = array();
        foreach(self::$prefs as $name=>$data) {
            $value = $data[1];
            if (self::$prefs[$name][0] == 'array') {
                // cas particulier des tableaux, il faut deserializer les donn�es
                $value = unserialize(base64_decode($value));
            }
            $return[$name] = $value;
        }
        return $return;
    }
    // }}}
}

/**
 * PreferencesByUser.
 *
 * Outil de gestion des pr�f�rences d'une application, avec une granularite
 * plus fine que Preferences: granularite du user.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @package Framework
 */
class PreferencesByUser extends Preferences
{
    // PreferencesByUser::getRealName() {{{

    /**
     * Utilitaire pour appeler les autres methodes de facon transparente
     *
     * @static
     * @access public
     * @param  string $name
     * @param  integer $userId
     * @return string
     */
    public static function getRealName($name, $userId=0) {
        $userId = (!$userId)?Auth::getUserId():$userId;
        return $name . '_' . $userId;
    }

    // }}}
    // PreferencesByUser::set() {{{

    /**
     * D�finit une Preference, attention si une pr�f�rence de ce nom existe d�j�
     * pour le meme user, elle est modifiee
     *
     * @static
     * @access public
     * @param  string $name
     * @param  string $value
     * @param  integer $userId
     * @return void
     */
    public static function set($name, $value, $userId=0) {
        $name = PreferencesByUser::getRealName($name, $userId);
        parent::set($name, $value);
    }

    // }}}
    // PreferencesByUser::get() {{{

    /**
     * R�cup�re une Preference.
     * Si default est renseign� et que la Preference n'existe pas c'est cette
     * valeur qui est retourn�e.
     *
     * @static
     * @access public
     * @param  string $name
     * @param  mixed  $default
     * @param  integer $userId
     * @return mixed integer, boolean, double, string or array
     */
    public static function get($name, $default=false, $userId=0) {
        $name = PreferencesByUser::getRealName($name, $userId);
        return parent::get($name, $default);
    }

    // }}}
    // PreferencesByUser::delete() {{{

    /**
     * Supprime une Preference.
     * Note: la pr�f�rence n'est supprim�e en base de donn�es que lors de
     * l'appel � la m�thode save().
     *
     * @static
     * @access public
     * @param  string $name
     * @param  integer $userId
     * @return void
     */
    public static function delete($name, $userId=0) {
        $name = PreferencesByUser::getRealName($name, $userId);
        parent::delete($name);
    }

    // }}}
}

?>
