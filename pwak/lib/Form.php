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
 * @version   SVN: $Id: Form.php,v 1.7 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class FormTools {
    /**
     * FormTools::WriteOptionsFromObject()
     * Prend en param�tres un nom d'objet et retourne un tableau
     * d'options de type:
     * <code>
     *         [0=>'<option value="1">Label1</option>',
     *          1=>'<option value="2">Label2</option>']
     * </code>
     *
     * @static
     * @param string $objname le nom de l'objet
     * @param mixed $selID la ou les valeurs selectionn�es par d�faut
     * @param mixed array or Filter: un filtre optionnel � appliquer
     * @param array $sort un tableau optionnel pour le tri
     * @param string $labelMethod la m�thode � utiliser pour le label
     * @param array $fields les attributs � r�cup�rer.
     * @param boolean $addNullEntry true pour ajouter une option 'S�lectionnez
     * un �l�ment'
     * @return array the options array
     */
    static function writeOptionsFromObject($objname, $selID=0,
        $filter=array(), $sort=array(), $labelmethod='toString', $fields=array(),
        $addNullEntry=false)
    {
        $options = array();
        $mapper = Mapper::singleton($objname);
        if (Tools::isException($mapper)) {
            return $mapper;
        }
        if ($labelmethod == 'toString') {
            $toStringAttribute = Tools::getToStringAttribute($objname);
            $fields = (is_array($toStringAttribute))?
                    $toStringAttribute:array($toStringAttribute);
        }
        $col = $mapper->loadCollection($filter, $sort, $fields);
        if ($col instanceof Collection) {
            $dataArray = array();
            $count = $col->getCount();
            for($i = 0; $i < $count; $i++) {
                $item = $col->getItem($i);
                if (method_exists($item, 'getId')
                        && method_exists($item, $labelmethod)) {
                    $dataArray[$item->getId()] = $item->$labelmethod();
                }
                unset($item);
            }
            $options = FormTools::writeOptionsFromArray($dataArray, $selID, $addNullEntry);
        }
        return $options;
    }

    /**
     * FormTools::writeOptionsFromCollection()
     * retourne un tableau d'options � partir de la collection pass�e en
     * param�tre et de la m�thode � afficher comme label du select.
     *
     * @static
     * @param Object Collection $col une collection d'objet
     * @param mixed $selID la ou les valeurs selectionn�es par d�faut
     * @param string $labelmethod m�thode � appel� pour le contenu de l'option
     * @param boolean $addNullEntry true pour ajouter une option 'S�lectionnez
     * un �l�ment'
     * @return void
     */
    static function writeOptionsFromCollection($col, $selID=0,
            $labelmethod='toString', $addNullEntry=false) {
        $options = array();
        if ($col instanceof Collection) {
            $dataArray = array();
            $count = $col->getCount();
            for($i = 0; $i < $count; $i++) {
                $item = $col->getItem($i);
                if (method_exists($item, 'getId') && method_exists($item, $labelmethod)) {
                    $dataArray[$item->getId()] = $item->$labelmethod();
                }
                unset($item);
            }
            $options = FormTools::writeOptionsFromArray($dataArray, $selID, $addNullEntry);
        }
        return $options;
    }

    /**
     * FormTools::writeOptionsFromArray()
     * retourne un tableau d'options � partir du tableau pass� en param�tre
     *
     * @static
     * @param array $array
     * @param integer $sel la valeur selectionn�e par d�faut
     * @param boolean $addNullEntry true pour ajouter une option 'S�lectionnez
     * un �l�ment
     * @return array
     */
    static function writeOptionsFromArray($array, $selID=0, $addNullEntry=false) {
        asort($array);  // asort, plutot que natcasesort, finalement
        $options = array();
        $selID = is_array($selID)?$selID:array($selID);
        if ($addNullEntry) {
            $options[] = '<option value="##">' . MSG_SELECT_AN_ELEMENT . '</option>';
        }
        foreach($array as $key=>$val) {
            $selected = (in_array($key, $selID))?' selected="selected"':'';
            $options[] = sprintf('<option value="%s"%s>%s</option>',
                $key, $selected, $val);
        }
        return $options;
    }

    /**
     * Fonction qui remplit un objet r�cursivement � partir de donn�es POST.
     * Ex:
     * pour un objet A qui a les propri�t�s suivantes:
     *     - name
     *     - child (un objet li�)
     * on devrait avoir comme donn�es POST:
     * - POST['A_name'] = 'toto'
     * - POST['A_child_ID'] = 0 (l'objet li� n'existe pas il faut le cr�er)
     * - POST['child_name'] = 'titi' soit les propri�t�s de l'objet li�...
     *
     * Cette fonction est donc valable pour les formulaires "d�normalis�s",
     * c'est � dire les formulaires dans lesquels on m�lange les propri�t�s
     * d'un objet et de ses objets li�s. pour un exemple, regarder
     * SiteAddEdit.php et le template correspondant
     * lib/templates/Site/SiteAddEdit.html
     * {@example SiteAddEdit.php}
     * et le template:
     * {@example lib/templates/Site/SiteAddEdit.html}
     * ### A appeler dans une transaction !
     *
     * @static
     * @param array $postdata les donn�es POST
     * @param object $baseObject l'objet de base
     * @param string $clsname nom de l'objet (si false, on se base sur
     * $baseObject pour le trouver)
     * @param string $prefix prefixe ajout� au cl� du tableau $postdata
     * @return void
     **/
    static function autoHandlePostData($postdata, $baseObject, $clsname=false,
        $prefix=''){
        if (!is_array($postdata)) {
            trigger_error('autoHandlePostData() need POST data.', E_USER_ERROR);
        }
        if (!is_object($baseObject)) {
            trigger_error('autoHandlePostData() need a base object', E_USER_ERROR);
        }
        $clsname = (false==$clsname)?get_class($baseObject):$clsname;
        foreach($baseObject->getProperties() as $property=>$class) {
            $setter = sprintf("set%s", $property);
            if (!method_exists($baseObject, $setter)) {
                continue;
            }
            $property = $prefix . $clsname . '_' . $property;
            if (!is_string($class) && isset($postdata[$property])) {
                // propri�t�s simples
                $baseObject->$setter($postdata[$property]);
            } else if(isset($postdata[$property . '_ID'])) {
                $id = $postdata[$property . '_ID'];
                if (isset($id) && intval($id) > 0) {
                    $childObject = Object::load($class, $id);
                    $baseObject->$setter($childObject);
                } else {
                    $baseObject->$setter($id);
                }
            }
        }
    }

    /**
     * Fonction qui remplit un objet r�cursivement � partir de donn�es POST.
     * Ex:
     * pour un objet A qui a les propri�t�s suivantes:
     *     - name
     *     - child (un objet li�)
     *     - XXXCollection de type *..*
     * on devrait avoir comme donn�es POST:
     * - POST['A_name'] = 'toto'
     * - POST['A_child_ID'] = 0 (l'objet li� n'existe pas il faut le cr�er)
     * - POST['child_name'] = 'titi' soit les propri�t�s de l'objet li�...
     * - POST['A_XXXCollection'] = array (le chps du form: name='A_XXXCollection[]')
     *
     * Cette fonction est donc valable pour les formulaires "d�normalis�s",
     * c'est � dire les formulaires dans lesquels on m�lange les propri�t�s
     * d'un objet et de ses objets li�s. pour un exemple, regarder
     * SiteAddEdit.php et le template correspondant
     * lib/templates/Site/SiteAddEdit.html
     * ### A appeler dans une transaction !
     *
     * @static
     * @param array $postdata les donn�es POST
     * @param object $baseObject l'objet de base
     * @param string $clsname nom de l'objet (si false, on se base sur
     * $baseObject pour le trouver)
     * @param string $prefix prefixe ajout� au cl� du tableau $postdata
     * @return void
     **/
    static function autoHandlePostDataWithLinks($postdata, $baseObject,
        $clsname=false, $prefix='') {
        if (!is_array($postdata)) {
            trigger_error('autoHandlePostDataWithLinks() need POST data.',
                    E_USER_ERROR);
        }
        if (!is_object($baseObject)) {
            trigger_error('autoHandlePostDataWithLinks() need a base object',
                    E_USER_ERROR);
        }
        $clsname = (false==$clsname)?get_class($baseObject):$clsname;
        /**
         * On remplit l'objet
         **/
        foreach($baseObject->getProperties() as $property=>$class) {
            $setter = sprintf("set%s", $property);
            if (!method_exists($baseObject, $setter)) {
                continue;
            }
            $property = $prefix . $clsname . '_' . $property;
            if (!is_string($class) && isset($postdata[$property])) {
                // propri�t�s simples
                $baseObject->$setter($postdata[$property]);
            } else if(isset($postdata[$property . '_ID'])) {
                $id = $postdata[$property . '_ID'];
                if (isset($id) && intval($id) > 0) {
                    $childObject = Object::load($class, $id);
                    $baseObject->$setter($childObject);
                } else {
                    $baseObject->$setter($id);
                }
            }
        }
        // Gestion des Collection attributes *..*
        foreach($baseObject->getLinks() as $property=>$detail) {
            $setter = sprintf("set%sCollectionIds", $property);
            if (!method_exists($baseObject, $setter)) {
                continue;
            }
            $property = $prefix . $clsname . '_' . $property.'Collection';
            if (isset($_REQUEST[$property]) && false != $_REQUEST[$property]) {
                if (!is_array($_REQUEST[$property])) {
                    $_REQUEST[$property] = array($_REQUEST[$property]);
                }
                $baseObject->$setter($_REQUEST[$property]);
            }
        }
    }

    /**
     * R�cup�re les valeurs par d�faut d'un formulaire construit avec QuickForm
     * � partir du formulaire et de l'objet �dit�.
     *
     * @static
     * @param Object QuickForm $QuickForm un formulaire QuickForm
     * @param Object $editedObject l'instance de l'objet edite
     * @param string $clsname classe de l'object �dit� (si false, on se base sur
     * $editedObject pour la trouver)
     * @return void
     */
    static function getDefaultValues($QuickForm, $editedObject, $clsname=false) {
        $defaultValues = array();
        $elements = array_keys($QuickForm->_elementIndex);
        $count = count($elements);
        $clsname = (false==$clsname)?get_class($editedObject):$clsname;
        for($i = 0; $i < $count; $i++) {
            $path = explode("_", $elements[$i]);
            $base = array_shift($path);
            if ($base != $clsname || empty($path)) {
                continue;
            }
            $value = Tools::getValueFromMacro($editedObject, '%'
                . implode(".", $path) . '%');
            $defaultValues[$elements[$i]] = ($value != 'N/A')?$value:'';
        }
        return $defaultValues;
    }

    /**
     * Retourne la chaine sans tags html et sans blancs
     *
     * @static
     * @param string $input
     * @return array
     */
    static function formatInput($input) {
        if (is_array($input)) {
            foreach($input as $key=>$val){
                $input[$key] = strip_tags(trim($val));
            }
            return $input;
        }
        return strip_tags(trim($input));
    }
}

?>
