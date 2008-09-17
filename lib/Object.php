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

class Object {
    // Constantes de types {{{

    /**
     * Constantes utilis�es pour les types de donn�es dans Object::getProperties()
     */
    const TYPE_STRING      = 1;
    const TYPE_INT         = 2;
    const TYPE_BOOL        = 3;
    const TYPE_FLOAT       = 4;
    const TYPE_DECIMAL     = 5;
    const TYPE_CONST       = 6;
    const TYPE_TEXT        = 7;
    const TYPE_LONGTEXT    = 8;
    const TYPE_DATETIME    = 9;
    const TYPE_DATE        = 10;
    const TYPE_TIME        = 11;
    const TYPE_FKEY        = 12;
    const TYPE_ONETOMANY   = 13;
    const TYPE_MANYTOMANY  = 14;
    const TYPE_FILE        = 15;
    const TYPE_PASSWORD    = 16;
    const TYPE_HTML        = 17;
    const TYPE_EMAIL       = 18;
    const TYPE_URL         = 19;
    const TYPE_IMAGE       = 20;
    const TYPE_FILE_UPLOAD = 21;
    const TYPE_HEXACOLOR   = 22;
    const TYPE_I18N_STRING = 23;
    const TYPE_I18N_TEXT   = 24;
    const TYPE_I18N_HTML   = 25;

    // }}}
    // Constantes de codes pour les exceptions {{{

    /**
     * Constantes utilis�es pour les codes des exceptions remont�es.
     */
    const EXC_ALREADY_EXISTS   = 1;
    const EXC_NOT_ALLOWED      = 2;
    const EXC_EMPTY_FOR_DELETE = 3;

    // }}}
    // propri�t�s publiques {{{

    /**
     * Indique si l'objet a �t� charg� � partir de la base de donn�es
     *
     * @access public
     * @var    boolean hasBeenInitialized
     */
    public $hasBeenInitialized = false;

    /**
     * Indique si l'objet a �t� charg� en mode lecture seule, ce qui
     * est le cas pour les objets charg�s en lazy loading
     *
     * @access public
     * @var    boolean readonly
     */
    public $readonly = false;

    /**
     * Id de database, uniquement pertinent pour les comptes avec une seule
     * base commune.
     *
     * @access public
     * @var    integer dbID
     */
    public $dbID = null;

    // }}}
    // propri�t�s private/protected {{{

    /**
     * L'identifiant unique de l'objet
     * XXX mis � public pour l'instant pour �viter les impacts
     *
     * @access public
     * @var    integer _Id
     */
    public $_Id = 0;

    // }}}
    // constructeur {{{

    /**
     * Constructeur
     *
     * @access public
     */
    public function __construct() {
    }

    // }}}
    // Object::useInheritance() {{{

    /**
     * D�termine si l'entit� est une entit� qui utilise l'h�ritage.
     * (classe parente ou classe fille). Ceci afin de differencier les
     * entit�s dans le mapper car classes filles et parentes sont mapp�es
     * dans la m�me table.
     *
     * @access public
     * @abstract
     * @static
     * @return boolean
     * @todo   voir si on peut/doit vraiment mettre static sans trop d'impacts
     */
    public static function useInheritance() {
        return false;
    }

    // }}}
    // Object::getClassName() {{{

    /**
     * Retourne le nom de la classe de l'instance en cours.
     *
     * @access public
     * @return string
     */
    public function getClassName() {
        return get_class($this);
    }

    // }}}
    // Object::getParentClassName() {{{

    /**
     * Retourne le nom de l'entit� parente ou false si pas d'h�ritage
     * m�thode surcharg�e donc dans les entit�s filles.
     *
     * @access public
     * @static
     * @return mixed boolean ou string
     * @todo   voir si on peut/doit vraiment mettre static sans trop d'impacts
     */
    public static function getParentClassName() {
        return false;
    }

    // }}}
    // Object::getId() {{{

    /**
     * Getter pour la propri�t� _Id
     *
     * @access public
     * @return integer
     */
    public function getId() {
        return $this->_Id;
    }

    // }}}
    // Object::setId() {{{

    /**
     * Setter pour la propri�t� _Id
     *
     * @access public
     * @param  integer $value
     * @return void
     */
    public function setId($value) {
        $this->_Id = (int)$value;
    }

    // }}}
    // Object::toString() {{{

    /**
     * Retourne la chaine de caract�re repr�sentant l'objet
     *
     * @access public
     * @return string
     */
    public function toString() {
        return method_exists($this, 'getName')?$this->getName():get_class($this);
    }

    // }}}
    // Object::toJSON() {{{

    /**
     * Retourne la repr�sentation de l'objet au format JSON.
     * Si fields n'est pas vide, les propri�t�s list�es seront aussi retourn�es
     * sinon seul l'id et la repr�sentation toString() le sont.
     *
     * Pour les attributs de type const, retourne un tableau:
     * array('value' => [valeur de la const.], 'label' => [label correspondant]
     * Pour les attributs de type FK, retourne un tableau:
     * array('id => [valeur de la FK], 'toString' => [toString correspondant]
     *
     * @access public
     * @param  array of strings fields liste des attributs � r�cup�rer
     * @return string
     */
    public function toJSON($fields = array()) {
        $array = array(
            'id' => $this->getId(),
            'toString' => utf8_encode($this->toString())
        );
        $properties = $this->getProperties();
        foreach ($fields as $name) {
            if (!isset($properties[$name])) {
                continue;
            }
            $type   = $properties[$name];
            $getter = 'get' . $name;
            $value  = $this->$getter();
            if ($type == Object::TYPE_CONST) {
                // attribut de type const
                $constArrayGetter = 'get' . $name . 'ConstArray';
                $constArray = $this->$constArrayGetter();
                $label = isset($constArray[$value])?
                    utf8_encode($constArray[$value]):'';
                $value = array('value' => $value, 'label' => $label);
            } else if (is_string($type)) {
                // attribut de type foreignkey
                if (!Tools::isEmptyObject($value)) {
                    $value = array(
                        'id' => $value->getId(),
                        'toString' => utf8_encode($value->toString())
                    );
                } else {
                    $value = array('id' => 0, 'toString' => '');
                }
            } else {
                $value = is_string($value)?utf8_encode($value):$value;
            }
            $name[0] = strtolower($name[0]);
            $array[$name] = $value;
        }
        return json_encode($array);
    }

    // }}}
    // Object::getToStringAttribute() {{{

    /**
     * Retourne le nom de l'attribut repr�sentant l'objet, point� par toString()
     *
     * @abstract
     * @static
     * @return array
     * @access public
     * @todo   voir si on peut/doit vraiment mettre static sans trop d'impacts
     */
    public function getToStringAttribute() {
        return (in_array('Name', array_keys($this->getProperties())))?'Name':'Id';
    }

    // }}}
    // Object::uuid() {{{

    /**
     * Retourne un id unique, utilis� pour le cache en m�moire
     *
     * @access public
     * @return string
     * @todo voir si on a encore besoin de ce cache en m�moire avec php5
     */
    public function uuid(){
        return md5(get_class($this) . $this->_Id);
    }

    // }}}
    // Object::getProperties() {{{

    /**
     * Retourne les propri�t�s "simples" et les foreignkeys de l'objet dans un
     * tableau, ex:
     * <code>
     * array(
     *     'Name'  => Object::TYPE_STRING, // le type est indiqu� par la constante
     *     'MyFoo' => 'Foo'        // 'Foo' indique une fkey vers l'objet 'Foo'
     * );
     * </code>
     *
     * @access public
     * @abstract
     * @static
     * @return array
     * @todo voir si on peut/doit vraiment mettre static sans trop d'impacts
     */
    public static function getProperties() {
        return array();
    }

    // }}}
    // Object::getLinks() {{{

    /**
     * Retourne les propri�t�s multiples 1..* ou *..* de l'objet
     * dans un tableau, ex:
     * <code>
     * array(
     *     // 1..*: ici 'linkClass' est l'objet contenant une fkey vers
     *     // notre objet, field est le nom de cette foreignkey
     *     'MyBar'=>array('linkClass'=>'Bar', field=>'Foo'),
     *     // *..*: ici 'linkClass' est l'objet li�, 'linkTable' est le
     *     // nom de la table de lien, 'field' est le nom du champs
     *     // repr�sentant notre objet dans cette table et 'linkField'
     *     // celui de l'objet li�
     *     'MyBoo'=>array('linkClass'=>'Boo', 'linkTable'=>'FooBoo',
     *                    'field'=>'lnkFoo', 'linkField'=>'lnkBoo')
     * );
     * </code>
     *
     * @access public
     * @abstract
     * @static
     * @return void
     * @todo   voir si on peut/doit vraiment mettre static sans trop d'impacts
     */
    public static function getLinks() {
        return array();
    }

    // }}}
    // Object::getTableName() {{{

    /**
     * Retourne le nom de la table de l'entit�
     *
     * @access public
     * @abstract
     * @static
     * @return string
     * @todo   voir si on peut/doit vraiment mettre static sans trop d'impacts
     */
    public static function getTableName() {
        return '';
    }

    // }}}
    // Object::dateFormat() {{{

    /**
     * Retourne la date mysql au format $format.
     *
     * Valeurs possibles de $format et type de retour:
     *      - 'timestamp': retourne un timestamp unix
     *      - 'quickform': retourne une date au format PEAR::QuickForm
     *      - 'localedate': retourne une date au format JJ/MM/AA HH:MM:SS (si fr)
     *      - 'localedate_short': retourne une date au format JJ/MM/AA (si fr)
     *      - une chaine accept�e par la fonction php "date"
     *        (cf. http://php.net/date)
     *
     * @access public
     * @param  string $value la valeur de la date au format MySQL
     * @param  string $format le format d�sir�
     * @return mixed
     */
    public function dateFormat($value, $format=false) {
        if (false == $format) {
            return $value;
        }
        switch ($format){
            case 'timestamp':
                return DateTimeTools::MySQLDateToTimeStamp($value);
            case 'quickform':
                return DateTimeTools::MySQLToQuickFormDate($value);
            case 'localedate':
                return I18N::formatDate($value);
            case 'localedate_short':
                return I18N::formatDate($value, I18N::DATE_LONG);
            default:
                $value = DateTimeTools::MySQLDateToTimeStamp($value);
                return date($format, $value);
        }
    }

    // }}}
    // Object::save() {{{

    /**
     * Sauve l'objet en base de donnees.
     *
     * @access public
     * @return boolean
     */
    public function save() {
        // Si l'objet contient au moins un attribut qui ne peut avoir la m�me
        // valeur pour 2 occurrences
        try {
            $this->canBeSaved();
        } catch(Exception $exc) {
            throw $exc;
        }
        $mapper = Mapper::singleton(get_class($this));
        return $mapper->save($this);
    }

    // }}}
    // Object::delete() {{{

    /**
     * Object::delete()
     * Detruit l'objet en base de donnees
     *
     * @access public
     * @return boolean
     */
    public function delete() {
        // Si l'objet ne peut �tre d�truit en base de donnees
        try {
           $this->canBeDeleted();
        } catch(Exception $exc) {
            throw $exc;
        }
        $mapper = Mapper::singleton(get_class($this));
        return $mapper->delete($this->getId());
    }

    // }}}
    // Object::getUniqueProperties() {{{

    /**
     * Retourne le tableau des propri�t�s qui ne peuvent prendre la m�me valeur
     * pour 2 occurrences.
     *
     * @static
     * @access public
     * @return array
     */
    public static function getUniqueProperties() {
        return array();
    }

    // }}}
    // Object::getEmptyForDeleteProperties() {{{

    /**
     * Retourne le tableau des propri�t�s doivent �tre "vides" (0 ou '') pour
     * qu'une occurrence puisse �tre supprim�e en base de donn�es.
     *
     * @static
     * @access public
     * @return array
     */
    public static function getEmptyForDeleteProperties() {
        return array();
    }

    // }}}
    // Object::canBeSaved() {{{

    /**
     * Object::canBeSaved()
     * Retourne true si l'objet peut �tre sauv� en base de donnees.
     * Se base sur l'unicit� �ventuelle des attributs via getUniqueProperties().
     *
     * @access public
     * @return boolean
     */
    public function canBeSaved() {
        $mapper = Mapper::singleton(get_class($this));
        $filter=array();
        if(defined('DATABASE_ID')) {
            if(!empty($this->dbID) && $this->dbID != DATABASE_ID) {
                throw new Exception(
                    _('You are not allowed to modify this record'),
                    self::EXC_NOT_ALLOWED
                );
            }
            $filter['DBId'] = DATABASE_ID;
        }
        foreach($this->getUniqueProperties() as $propertyName){
            $getter = 'get' . $propertyName;
            $fullFilter = $filter;
            $fullFilter[$propertyName] = $this->$getter();
            $test = $mapper->load($fullFilter);
            if ($test instanceof Object && ($test->_Id != $this->_Id)) {
                throw new Exception(
                    sprintf(CANT_BE_SAVED, get_class($this)),
                    self::EXC_ALREADY_EXISTS
                );
            }
        }
    	return true;
    }

    // }}}
    // Object::canBeDeleted() {{{

    /**
     * Object::canBeDeleted()
     * Retourne true si l'objet peut �tre d�truit en base de donnees.
     * Se base sur le fait que des attributs, s'ils sont renseign�s, emp�chent
     * l'ocurrence d'�tre d�truite
     *
     * @access public
     * @return boolean
     */
    public function canBeDeleted() {
        if(defined('DATABASE_ID')) {
            if(!empty($this->dbID) && $this->dbID != DATABASE_ID) {
                throw new Exception(
                    _('You are not allowed to modify this record'),
                    self::EXC_NOT_ALLOWED
                );
            }
        }
        $links = $this->getLinks();
        foreach($this->getEmptyForDeleteProperties() as $propertyName){
            if(isset($links[$propertyName])) {
                $getter = 'get' . $propertyName . 'CollectionIds';
                $value = $this->$getter();
                if(count($value)>0) {
                    throw new Exception(
                        CANT_BE_DELETED,
                        self::EXC_EMPTY_FOR_DELETE
                    );
                }
            } else {
                $getter = 'get' . $propertyName;
                $value = $this->$getter();
                if (!empty($value)) {
                    throw new Exception(
                        CANT_BE_DELETED,
                        self::EXC_EMPTY_FOR_DELETE
                    );
                }
            }
        }
        return true;
    }

    // }}}
    // Object::generateId() {{{

    /**
     * "R�serve" le prochain id disponible pour la classe de l'objet, l'assigne
     * � l'objet et le retourne.
     * Cela permet de connaitre l'id (la valeur de la cl� primaire) d'un objet
     * avant sa sauvegarde en base de donn�es.
     *
     * @access public
     * @return integer
     */
    public function generateId() {
        $mapper = Mapper::singleton(get_class($this));
        $id = $mapper->generateId();
        $this->setId($id);
        return $id;
    }

    // }}}
    // Object::load() {{{

    /**
     * Instancie un objet de la classe $name ou  retourne une exception
     *
     * @access public
     * @static
     * @see    Mapper::buildObject()
     * @param  string $name le nom de la classe concernee
     * @return object
     */
    public static function load($name, $id=false, $fields=array(),
        $noCache=false) {
        if (false == $id) {
            require_once(MODELS_DIR . '/' . $name . '.php');
            return new $name();
        }
        $mapper = Mapper::singleton($name);
        return $mapper->load(
            (is_array($id) || $id instanceof FilterComponent)?
                $id:array('Id' => $id),
            $fields,
            $noCache
        );
    }

    // }}}
    // Object::loadCollection() {{{

    /**
     * Retourne une collection d'objets de la classe $name.
     * Voir Core/Mapper.php (loadObjectCollection) pour plus d'infos.
     *
     * @access public
     * @static
     * @see    Mapper::loadObjectCollection()
     * @param  mixed   $filter un tableau ou un objet filtre
     * @param  array   $order un tableau pour les tris
     * @param  array   $fields un tableau de chaines pour les champs � charger
     * @param  integer $rows le nombre de lignes � charger (pagination)
     * @param  integer $page l'index de la page en cours (pagination)
     * @param  integer $limit le nombre d'enregistrements � charger (LIMIT)
     * @return object Collection
     */
    public static function loadCollection($name, $filter=array(),
        $order=array(), $fields=array(), $rows=0, $page=1, $limit=false,
        $noCache=false)
    {
        $mapper = Mapper::singleton($name);
        return $mapper->loadCollection($filter, $order, $fields, $rows,
            $page, $limit, $noCache);
    }

    // }}}
    // object::__get() {{{

    /**
     * __get
     *
     * @param string $property
     * @access public
     * @return mixed
     */
    public function __get($property) {
        $getter = 'get' . $property;
        if(method_exists($this, $getter)) {
            return $this->$getter();
        }
        return $this->$property;
    }

    // }}}
    // Object::__set() {{{

    /**
     * __set
     *
     * @param string $property
     * @param mixed $value
     * @access public
     * @return mixed
     */
    public function __set($property, $value) {
        $setter = 'set' . $property;
        if(method_exists($this, $setter)) {
            return $this->$setter($value);
        }
        return $this->$property = $value;
    }

    // }}}
    // Object::isPublicEntity() {{{

    /**
     * return true si l'entit� est public et accessible via le Master
     *
     * @param string $entity Entit� � chercher
     * @return bool
     * @access public
     */
    public static function isPublicEntity($entity) {
        if($entity == 'Entity') {
            return true;
        }
        $e = Object::load('Entity', array('Name' => $entity, 'Public'=>1));
        return DATABASE_ID == 0 && $e instanceof Entity;
    }

    // }}}
}

?>
