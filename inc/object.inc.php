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
 * @package core
 * @version $Id: object.inc.php,v 1.1 2004/09/13 13:22:06 alberto Exp $
 */

/**
 * Print the error message in the constructor.
 *
 * The execution is interrupted.
 */
define('ERROR_ACTION_ABORT', 1);

/**
 * Print the error message in the constructor.
 *
 * The execution is not interrupted.
 */
define('ERROR_ACTION_PRINT', 2);

/**
 * Sent the error message in the constructor to PHP's system logger.
 *
 * Operating System's system logging mechanism or a file is used, depending on
 * what the error_log configuration directive is set to.
 */
define('ERROR_ACTION_LOGIN', 3);

/**
 * Send the error message in the constructor to specified email address.
 *
 * This error action uses the same internal function as PHP's mail() function
 * does. The execution is not interrupted.
 */
define('ERROR_ACTION_EMAIL', 4);

/**
 * The error message is sent through the PHP debugging connection.
 *
 * This option is only available if PHP remote debugging has been enabled.
 * The execution is not interrupted.
 */
define('ERROR_ACTION_DEBUG', 5);

/**
 * The error message is appended to specified file in the constructor.
 *
 * The execution is not interrupted.
 */
define('ERROR_ACTION_WRITE', 6);

/**
 * Use PHP's trigger_error() function to raise an internal error in PHP.
 *
 * The execution is aborted if you have defined your own PHP error handler or if
 * you set the error severity to E_USER_ERROR.
 */
define('ERROR_ACTION_RAISE', 7);

/**
 * Base class for other XSPHPLib classes.
 *
 * Normally you never make an instance of the Object class directly, you use it
 * by subclassing it.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 */
class Object {

    /**
     * Object class constructor.
     *
     * Call it from the constructor of every class inheriting the Object class.
     *
     * @access public
     * @return void
     */
    function Object() {
    }

    /**
     * Retrieves the name of the class of the object.
     *
     * Returns the name of the class of which the object is an instance.
     *
     * @access public
     * @return string
     */
    function className() {
        return get_class($this);
    }

    /**
     * Retrieves the parent class name for the object.
     *
     * Returns the name of the parent class of the class of which object is an instance.
     *
     * @access public
     * @return string
     */
    function classParent() {
        return get_parent_class($this);
    }

    /**
     * Retrieves the class methods names of the object.
     *
     * Returns the list of method names defined for the class of which object is an instance.
     *
     * @access public
     * @return array
     */
    function classMethods() {
        return get_class_methods($this->className());
    }

    /**
     * Retrieves the class properties names of the object.
     *
     * Returns the list of properties names defined for the class of which object is an instance.
     *
     * @access public
     * @return array
     */
    function classProperties() {
        return get_class_vars($this->className());
    }

    /**
     * Check if the objects inherits from the specified class.
     *
     * Returns true if the object has specified class as one of its parents, false otherwise.
     *
     * @access public
     * @param string $class the parent class name.
     * @return boolean
     */
    function inheritsFrom($class) {
        return is_subclass_of($class);
    }

}

/**
 * Error implements a class for reporting portable error messages.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 */
class Error extends Object {

	/**
	 * Error code
     * @access private
     * @var int
	 */
    var $_code;
    
	/**
  	 * Error message
     * @access private
	 * @var string
	 */
    var $_message;
    
    /**
     * Error class constructor.
     *
     * The $reply array should has action and option keys eg.
     * $reply = array('action' => ERROR_ACTION_WRITE, 'option' => 'logfile.log');
     * Action key can be set with one of the following constants:
     * {@link ERROR_ACTION_ABORT}, {@link ERROR_ACTION_PRINT},
     * {@link ERROR_ACTION_LOGIN}, {@link ERROR_ACTION_EMAIL},
     * {@link ERROR_ACTION_DEBUG}, {@link ERROR_ACTION_WRITE},
     * {@link ERROR_ACTION_RAISE}.
     *
     * @access public
     * @param string $message error message, defaults to "Unexpected Error".
     * @param int $code error code (optional), defaults to 0.
     * @param array $reply error mode of operation.
     * @return void
     */
    function Error($message='', $code=0, $reply=null) {
        Object::Object();
        $this->_code = $code;
        if (strlen($message) > 0) {
            $this->_message = $message;
        } else {
            $this->_message = 'Unexpected Error';
        }
        if (is_array($reply)) {
            if (empty($reply['format'])) {
                $format = "[%d] %s";
            } else {
                $format = $reply['format'];
            }
            if ($reply['action'] == ERROR_ACTION_WRITE) {
                $format = date('[d-m-Y H:i:s] ') . $format . "\n";
            }
            $message = sprintf($format, $this->_code, $this->_message);
            switch($reply['action']) {
                case ERROR_ACTION_ABORT:
                    die($message);
                    break;
                case ERROR_ACTION_PRINT:
                    print($message);
                    break;
                case ERROR_ACTION_LOGIN:
                    error_log($message, 0);
                    break;
                case ERROR_ACTION_EMAIL:
                    error_log($message, 1, $reply['option']);
                    break;
                case ERROR_ACTION_DEBUG:
                    error_log($message, 2, $reply['option']);
                    break;
                case ERROR_ACTION_WRITE:
                    error_log($message, 3, $reply['option']);
                    break;
                case ERROR_ACTION_RAISE:
                    trigger_error($message, $reply['option']);
                    break;
            }
        }
    }
    
    /**
     * Get the error code from the error object.
     *
     * Returns the error code.
     *
     * @access public
     * @return int
     */
    function getCode() {
        return $this->_code;
    }

    /**
     * Get the error message from the error object.
     *
     * Returns the error message.
     *
     * @access public
     * @return string
     */
    function getMessage() {
        return $this->_message;
    }

}

/**
 * Creates new object of the specified class.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @param string $class class name.
 * @param array $params class constructor params (optional), defaults to null.
 * @return object
 */
function &object_create($class, $params=null) {
    if (isset($params)) {
        if (is_array($params)) {
            $idents = array();
            while (list($ident) = each($params)) {
                $idents[] = "\$params[$ident]";
            }

            eval("\$result = new {$class}(" . join(', ', $idents) . ');');

        } else {
            $result = new $class($params);
        }
    } else {
        $result = new $class();
    }
    return $result;
}

/**
 * Check if specified object is or inherits from the error class.
 *
 * Returns true if the object is or inherits from error class, false otherwise.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @param object $data object reference.
 * @return boolean
 */
function object_isError($data) {
    if (is_object($data)) {
        return (get_class($data) == 'error' || is_subclass_of($data, 'error'));
    } else {
        return false;
    }
}

/**
 * Check if specified object is or inherits from the specified class.
 *
 * Returns true if the object is or inherits from specified class, false otherwise.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @param object $data object reference.
 * @param string $class class name.
 * @return boolean
 */
function object_isClass($data, $class) {
    if (is_object($data)) {
        return (get_class($data) == $class || is_subclass_of($data, $class));
    } else {
        return false;
    }
}

/**
 * Check if specified variable is error object and handle it.
 *
 * Check if specified variable is error if so it takes specific action according
 * to the reply values. By default it raises the exception using
 * {@link ERROR_ACTION_RAISE} reply action constant. The reply array should has
 * action  and option keys eg. $reply = array('action' => ERROR_ACTION_WRITE,
 * 'option' => 'logfile.log'); Action key can be set with one of the following
 * constants: {@link ERROR_ACTION_ABORT}, {@link ERROR_ACTION_PRINT},
 * {@link ERROR_ACTION_LOGIN}, {@link ERROR_ACTION_EMAIL},
 * {@link ERROR_ACTION_DEBUG},{@link ERROR_ACTION_WRITE},
 * {@link ERROR_ACTION_RAISE}.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @param mixed $data
 * @param array $reply error mode of operation.
 * @return void
 */
function object_checkError($data, $reply=null) {
    global $object_errorReply;
    if (object_isError($data)) {
        if (!is_array($reply)) {
            if (isset($object_errorReply)) {
                $reply = $object_errorReply;
            } else {
                $reply = array('action'=>ERROR_ACTION_RAISE, 'format'=>"[%d] %s", 'option'=>E_USER_ERROR);
            }
        }
        $error = new Error($data->getMessage(), $data->getCode(), $reply);
    }
}

?>
