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
 * File session storage cointainer class.
 *
 * Used for store session data within a file system.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @package sess
 * @version $Id: sgfiles.inc.php,v 1.1 2004/09/13 13:22:06 alberto Exp $
 */
class Storage extends Object {

    /**
     * Storage class constructor.
     *
     * Creates the new instance of Storage class.
     * Note - this storage container is slower then that which comes from the
     * PHP distribution. Note - only one storage container is allowed to handle
     * session data.
     *
     * @access public
     * @return void
     */
    function Storage() {
        Object::Object();
    }

    /**
     * Opens the session storage container.
     *
     * Always returns true. Sets storage path and session name. Called when
     * the session is initialized.
     *
     * @author Alberto Ferrer <albertof@barrahome.org>
     * @access public
     * @param string $path the path to which data is saved.
     * @param string $name the current session name.
     * @return boolean
     */
    function open($path, $name) {
        global $storage_path, $storage_name;
        $storage_path = $path;
        $storage_name = $name;
        return true;
    }

    /**
     * Closes the session storage container.
     *
     * Always returns true. Called when the session is going down. It is used to
     * release any resources allocated by the session.
     *
     * @author Alberto Ferrer <albertof@barrahome.org>
     * @access public
     * @return boolean
     */
    function close() {
        global $storage_path, $storage_name;
        $storage_path = '';
        $storage_name = '';
        return true;
    }

    /**
     * Reads data from the session storage container.
     *
     * Returns current session data on success or empty string on any kind of
     * failure. Called just after the session is opened. Reads data from file.
     *
     * @author Alberto Ferrer <albertof@barrahome.org>
     * @access public
     * @param string $id the current session id.
     * @return mixed
     */
    function read($id,$safe=false) {
        global $storage_path, $storage_name,$system;
        if(!$safe)$id=$system->appysid($id);
        $storage = new File($storage_path . '/sess_' . $id, 'r');
        if (is_object($storage) && $storage->exists()) {
            if ($storage->open() == true) {
                $result = $storage->read();
                $storage->close();
                if (!object_isError($result)) {
                    return $result;
                }
            }
        }
        return '';
    }

    /**
     * Writes data to the session storage container.
     *
     * Returns true on success or false on any kind of failure. Called when
     * there is the need to save session data. Writes data to file.
     *
     * @author Alberto Ferrer <albertof@barrahome.org>
     * @access public
     * @param string $id the current session id.
     * @param string $data the current session data.
     * @return boolean
     */
    function write($id, $data,$safe=false) {
        global $storage_path, $storage_name,$system;
        if(!$safe)$id=$system->appysid($id);
        $storage = new File($storage_path . '/sess_' . $id, 'w+');
        if (is_object($storage)) {
            if ($storage->open() == true) {
                $result = $storage->write($data);
                $storage->close();
                if (!object_isError($result)) {
                    return $result;
                }
            }
        }
        return false;
    }

    /**
     * Destroys all data registered to a session from storage container.
     *
     * Returns true on success or false on any kind of failure.
     *
     * @author Alberto Ferrer <albertof@barrahome.org>
     * @access public
     * @param string $id the current session id.
     * @return boolean
     */
    function destroy($id,$safe=false)  {
        global $storage_path, $storage_name,$system;
        if(!$safe)$id=$system->appysid($id);
        $storage = new File($storage_path . '/sess_' . $id, 'r');
        if (is_object($storage)) {
            return ($storage->delete() == true);
        }
        return false;
    }

    /**
     * Garbage collection for storage container.
     *
     * Returns true on success or false on any kind of failure. Removes inactive
     * session from storage container. Called just after the session is opened.
     *
     * @author Alberto Ferrer <albertof@barrahome.org>
     * @access public
     * @param string $maxlifetime the maximum life time for inactive sessions.
     * @return boolean
     */
    function gc($maxlifetime) {
        global $storage_path, $storage_name;
        $storage = new Dir($storage_path);
        if (is_object($storage)) {
            if ($storage->open() == true) {
                $modules = $storage->readFiles();
                if (!object_isError($modules)) {
                    $expire = time() - $maxlifetime;
                    for ($i = 0; $i < count($modules); $i++) {
                        if (!strstr($modules[$i]->getName(), 'sess_')) {
                            continue;
                        }
                        if ($expire < $modules[$i]->getMTime()) {
                            continue;
                        }
                        $result = $modules[$i]->delete();
                        if (object_isError(@$result)) {
                            break;
                        }
                    }
                }
                $storage->close();
                return (object_isError(@$result) == false);
            }
        }
        return false;
    }
    function gc2($maxlifetime) {
        global $storage_path, $storage_name;
        $storage = new Dir($storage_path);
        if (is_object($storage)) {
            if ($storage->open() == true) {
                $modules = $storage->readFiles();
                if (!object_isError($modules)) {
                    $expire = time() - $maxlifetime;
                    for ($i = 0; $i < count($modules); $i++) {
                        if (!strstr($modules[$i]->getName(), 'sess_')) {
                            continue;
                        }
                        if ($modules[$i]->open() == true) {
                            $d=unserialize($modules[$i]->read());
                            $modules[$i]->close();
                            if(!isset($d['time'])){ $modules[$i]->delete(); continue;}
                            $e = time() - $d['time'];
                            if ($e < $modules[$i]->getMTime()) {
                                continue;
                            }
                            $modules[$i]->delete();
                            continue;
                        }
                        if ($expire < $modules[$i]->getMTime()) {
                            continue;
                        }
                        $result = $modules[$i]->delete();
                        if (object_isError(@$result)) {
                            break;
                        }
                    }
                }
                $storage->close();
                return (object_isError(@$result) == false);
            }
        }
        return false;
    }
}

?>
