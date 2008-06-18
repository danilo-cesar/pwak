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

class TextTools {
    /**
     * entityDecode()
     *  
     * D�code les entit�s HTML telles que &nbsp; &pound; etc...
     * Ici on ajoute la gestion de l'euro, pas g�r� par la fonction native 
     * html_entity_decode().
     * 
     * @static
     * @access public
     * @param  string $str la cha�ne � d�coder
     * @return string la cha�ne d�cod�e
     **/
    static function entityDecode($str){
        return str_replace('&euro;', '�', html_entity_decode($str));
    }
    
    /**
     * Tronque un texte � $maxlen caract�res.
     * Si $tail est une chaine de carat�res elle est mise � la fin du texte.
     * 
     * Example: 
     * <code>
     * echo TextTools::truncateText(
     *     'Une longue phrase que l\'on d�sire couper', 10, '...');
     * // affiche: Une lon...
     * </code>
     *
     * @static
     * @access public
     * @param  integer $maxlen
     * @param  string  $tail
     * @return string
     */
    static function truncateText($text, $maxlen = 40, $tail = ''){
        $rmaxlen = $tail!=''?$maxlen-strlen($tail):$maxlen;
        return strlen($text)<=$maxlen?$text:substr($text, 0, $rmaxlen).$tail;
    }
}
?>
