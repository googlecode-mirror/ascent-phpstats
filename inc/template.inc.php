<?php
//
// +--------------------------------------------------------------------------+
// |                                                                          |
// |                     SkyTeam  Content Management System                   |
// |                                                                          |
// |                   Copyright (c) 2001-2006 SkyTeam.                       |
// |                                                                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or modify it  |
// | under the terms of the GNU General Public License as published by the    |
// | Free Software Foundation; either version 2 of the License, or any later  |
// | version.                                                                 |
// |                                                                          |
// | This program is distributed in the hope that it will be useful, but      |
// | WITHOUT ANY WARRANTY; without even the implied warranty of               |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General |
// | Public License for more details.                                         |
// |                                                                          |
// | You should have received a copy of the GNU General Public License along  |
// | with this program; if not, write to the Free Software Foundation, Inc.,  |
// | 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA                    |
// |                                                                          |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Autor: Alberto Ferrer <albertof@arrobagn.com>                            |
// |                                                                          |
// +--------------------------------------------------------------------------+
//


/**
 * File template manipulation class.
 *
 * Supports file template parsing, dynamic blocks, multiple variables storing
 * and parsing and many others advanced features.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @package core
 * @version $Id: template.inc.php,v 1.1 2004/09/13 13:22:06 alberto Exp $
 */
class Template extends Object {

	/**
	 * Root directory for template files.
     * @access private
	 * @var string
	 */
    var $_root;
    
	/**
	 * Template files paths.
     * @access private
	 * @var array
	 */
    var $_files;
    
	/**
	 * Param orginal values.
     * @access private
	 * @var array
	 */
    var $_params;
    
	/**
	 * Param prepared for parse.
     * @access private
	 * @var array
	 */
    var $_values;

    /**
     * Template class constructor.
     *
     * Creates the new instance of Template class and sets up basic properties.
     *
     * @access public
     * @param string $root the root to template files, defaults to "".
     * @return void
     */
    function Template($root='') {
        Object::Object();
        if (!is_dir($root)) {
            $this->_root = '';
        } else {
            $slash = substr($root, -1);
            if (ord($slash) != 47) {
                $root = $root . chr(47);
            }
            $this->_root = $root;
        }
        $this->_files = array();
        $this->_params = array();
        $this->_values = array();
    }

    /**
     * Gets the template files root.
     *
     * Returns the templates root or empty string if the path has not been set
     * in object constructor.
     *
     * @access public
     * @return string
     */
    function getRoot() {
        return $this->_root;
    }

    /**
     * Gets the template file path.
     *
     * Returns the template file path or empty string.
     *
     * @access public
     * @param string $handle the template file variable name.
     * @return string
     */
    function getFile($handle) {
        if (isset($this->_files[$handle])) {
            return $this->_files[$handle];
        }
        return '';
    }

    /**
     * Gets the template variable.
     *
     * Returns the template variable or empty string.
     *
     * @access public
     * @param string $handle the template variable name.
     * @return string
     */
    function getParam($handle) {
        if (isset($this->_values[$handle])) {
            return $this->_values[$handle];
        }
        return '';
    }

    /**
     * Gets the template dynamic block variable.
     *
     * Returns the template variable or empty string.
     *
     * @access public
     * @param string $parent the parent template variable name.
     * @param string $handle the template dynamic block variable name.
     * @return string
     */
    function getBlock($parent, $handle) {
        if (preg_match_all('/<!--Begin' . $handle . '-->(.*)\n\s*<!--End' . $handle . '-->/sm', $this->_values[$parent], $match)) {
            return $match[1][0];
        }
        return '';
    }

    /**
     * Sets the template files root.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $root the root to template files.
     * @return mixed
     */
    function setRoot($root) {
        if (is_dir($root)) {
            $slash = substr($root, -1);
            if (ord($slash) != 47) {
                $root = $root . chr(47);
            }
            $this->_root = $root;
        } else {
            return new Error('setroot(): Template root dir is not a directory.');
        }
        return true;
    }

    /**
     * Sets the template file path.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $handle the template file variable name.
     * @param string $value the template file variable value.
     * @return mixed
     */
    function setFile($handle, $value) {
        if (strlen($handle)) {
            $source = $this->_root . $value;
            if (file_exists($source)) {
                $this->_files[$handle] = $value;
            } else {
                return new Error("setfile(): Template file does not exists.");
            }
        } else {
            return new Error("setfile(): Template file handle name is empty.");
        }
        return true;
    }

    /**
     * Sets the template variable.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $handle the template variable name.
     * @param string $value the template variable value.
     * @return mixed
     */
    function setParam($handle, $value) {
        if (strlen($handle)) {
            $this->_values[$handle] = $value;
            $this->_params[$handle] = '/' . preg_quote('{' . $handle . '}') . '/';
        } else {
            return new Error("setparam(): Template param name is empty.");
        }
        return true;
    }

    /**
     * Sets the template dynamic block variable.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $parent the parent template variable name.
     * @param string $handle the template dynamic block variable name.
     * @param string $name the new template dynamic block variable name, defaults to $handle.
     * @return mixed
     */
    function setBlock($parent, $handle, $name='') {
        if (strlen($parent) && strlen($handle)) {
            $param = $this->getParam($parent);
            $block = $this->getBlock($parent, $handle);
            if (strlen($param) && strlen($block)) {
                if (strlen($name) > 0) {
                    $name = '{' . $name . '}';
                } else {
                    $name = '{' . $handle . '}';
                }
                $param = preg_replace('/<!--Begin' . $handle . '-->(.*)\n\s*<!--End' . $handle . '-->/sm', $name, $param);
                $this->setParam($parent, $param);
                $this->setParam($handle, $block);
            } else {
                return new Error("setblock(): Template param or block does not exists.");
            }
        } else {
            return new Error("setblock(): Template param or block name is empty.");
        }
        return true;
    }

    /**
     * Gets content of template file and sets it as template param.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $handle the template file variable name.
     * @return mixed
     */
    function parseFile($handle) {
        if (strlen($this->_files[$handle])) {
            $source = $this->_root . $this->_files[$handle];
            if (file_exists($source)) {
                $pattern = implode('', @file($source));
                if (strlen($pattern)) {
                    $this->setParam($handle, $pattern);
                } else {
                    return new Error("parsefile(): Template file is empty.");
                }
            } else {
                return new Error("parsefile(): Template file does not exists.");
            }
        } else {
            return new Error("parsefile(): Invalid template file handle.");
        }
        return true;
    }
    
    /**
     * Clears nested dynamic block from template variable.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $parent the parent template variable name.
     * @param string $handle the template dynamic block variable name.
     * @return mixed
     */
    function clearBlock($parent, $handle) {
        if (strlen($parent) && strlen($handle)) {
            $param = $this->getParam($parent);
            $block = $this->getBlock($parent, $handle);
            if (strlen($param) && strlen($block)) {
                $param = preg_replace('/<!--Begin' . $handle . '-->(.*)\n\s*<!--End' . $handle . '-->/sm', '', $param);
                $this->setParam($parent, $param);
            } else {
                return new Error("clearblock(): Template param or block does not exists.");
            }
        } else {
            return new Error("clearblock(): Template param or block name is empty.");
        }
        return true;
    }

    /**
     * Parses content of template variable.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure. Tries to find all variables nested in template variable
     * and sets appropirate values. The $target and $append params are used when
     * working with dynamic blocks, enables to store parsed values and adds next
     * results.
     *
     * @access public
     * @param string $handle the template variable name.
     * @param string $target the template variable name which stores parsed
     * variable content, defaults to $handle.
     * @param boolean $append indicates whether to append parsed content to
     * variable value, defaults to false.
     * @return mixed
     */
    function parseParam($handle, $target='', $append=false) {
        if (strlen($handle)) {
            if (empty($target)) {
                $target = $handle;
            }
            $pattern = preg_replace($this->_params, $this->_values, $this->_values[$handle]);
            if ($append && isset($this->_values[$target])) {
                $pattern = $this->_values[$target] . $pattern;
            }
            $this->setParam($target, $pattern);
        } else {
            return new Error("parseparam(): Template param name is empty.");
        }
        return true;
    }

    /**
     * Prints the template variable.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $handle the template variable name.
     * @param boolean $compress indicates whether to remove newlines characters,
     * , defaults to true.
     * @return mixed
     */
    function printParam($handle, $compress=true) {
        if (strlen($handle)) {
            $result = $this->_values[$handle];
            if ($compress) {
                $result = str_replace("\r", '', $result);
                $result = str_replace("\n", '', $result);
            }
            return print $result;
        } else {
            return new Error("printparam(): Template param name is empty.");
        }
    }

}

?>
