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
 * @version   SVN: $Id: GridActionRenderer.php,v 1.7 2008-05-30 09:23:47 david Exp $
 * @link      http://pwak.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

class GridActionRenderer {
    // properties {{{

    /**
     * Nom du formulaire qui va recevoir l'action .
     * 
     * @var string
     * @access public
     */
    public $formName;

    /**
     * Type de renderer (Button|Select|SelectForSingleSelection|Glyph)
     * 
     * @var string
     * @access public
     */
    public $type = 'Button';

    // }}}
    // GridActionRenderer::__construct() {{{

    /**
     * __construct 
     * 
     * @param string nom du formulaire qui va recevoir l'action. 
     * @access public
     * @return void
     */
    public function __construct($form) {
        $this->formName = $form;
    }

    // }}}
    // GridActionRenderer::render() {{{

    /**
     * Renderer par d�faut des actions, utilise pour chaque action sa m�thode 
     * render(). 
     * 
     * @param array $actions tableau d'objets action
     * @access public
     * @return void
     */
    public function render($actions) {
        $actions = (!is_array($actions))?array($actions):$actions;
        $result = array();
        $inSelect = array();
        $inSelectForSingleSelection = array();
        foreach($actions as $anAction) {
            if($anAction->renderer == 'Inline') {
                continue;
            }
            // Les actions dans un select sont regroup�es � la fin 
            if($anAction->renderer == 'Select') {
                $inSelect[] = $anAction;
                continue;
            }
            if($anAction->renderer == 'SelectForSingleSelection') {
                $inSelectForSingleSelection[] = $anAction;
                continue;
            }
            // v�rifie si un autre renderer doit �tre utilis�
            // (m�thode buttonRenderer() de l'action par exemple)
            $html = $this->checkRenderer($anAction);
            if($html != false) {
                $result[] = $html;
                continue;
            }
            // instancie le bon GridActionRenderer
            $cls = 'GridActionRenderer' . $anAction->renderer;
            if(class_exists($cls, true)) {
                $renderer = new $cls($this->formName);
                $result = array_merge($result, $renderer->render($anAction));
            } elseif(method_exists($anAction, 'render')) {
                // utilise le render de l'action
                $result[] = $anAction->render();
            }
        }
        // effectue le render des actions du select
        if(!empty($inSelect)) {
            $renderer = new GridActionRendererSelect($this->formName);
            $result = array_merge($result, $renderer->render($inSelect));
        }
        if(!empty($inSelectForSingleSelection)) {
            $renderer = new GridActionRendererSelectForSingleSelection($this->formName);
            $result = array_merge($result, $renderer->render($inSelectForSingleSelection));
        }
        return $result;
    }

    // }}}
    // GridActionRenderer::checkRendererProperty() {{{

    /**
     * V�rifie si l'action doit �tre forc� � utiliser un autre renderer.
     *
     * V�rifie si la propri�t� $renderer de l'action � la m�me 
     * valeur que $type, si oui la m�thode retourne false, sinon elle appelle la 
     * m�thode render() de l'action et retourne le r�sultat.
     * 
     * @param mixed $action 
     * @access public
     * @return mixed
     */
    public function checkRendererProperty($action) {
        if($action->renderer != $this->type) {
            return $action->render();
        }
        return false;
    }

    // }}}
    // GridActionRenderer::checkRendererMethod() {{{

    /**
     * V�rifie si une m�thode custom est pr�vu pour le render de l'action.
     *
     * Cette m�thode doit s'appeler ButtonRender() pour un GridActionRenderer avec le 
     * type Button, SelectRenderer() si le type est Select, etc. Si l'action ne 
     * poss�de pas cette m�thode la fonction retourne true, sinon elle l'appelle 
     * et retourne le r�sultat.
     * 
     * @param mixed $action 
     * @access public
     * @return mixed
     */
    public function checkRendererMethod($action) {
        $method = $this->type . 'Renderer';
        if(method_exists($action, $method)) {
            return $action->$method();    
        }
        return false;
    }

    // }}}
    // GridActionRenderer::checkRenderer() {{{

    /**
     * V�rifie si l'action doit utiliser un autre renderer.
     *
     * Appelle la m�thode checkRendererProperty() puis soit retourne le r�sultat 
     * s'il n'est pas nul soit retourne le r�sultat de l'appel � checkRendererMethod().
     * 
     * @param mixed $action 
     * @access public
     * @return mixed
     */
    public function checkRenderer($action) {
        $html = $this->checkRendererProperty($action);
        if($html !== false) {
            return $html;
        }
        return $this->checkRendererMethod($action);
    }

    // }}}
}

?>
