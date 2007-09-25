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
 * The sort order used during subdirectory sorting operations.
 *
 * This variable shouldn't be modified. It is used internally by
 * {@link Dir::read()}, {@link Dir::readDirs()} methods.
 *
 * @global string $dir_sortOrder
 *
 */
$dir_sortOrder = 'asc';

/**
 * Directory manipulation class.
 *
 * Dir class id used for creating, opening, closing and removing directories.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @package io
 * @version $Id: dir.inc.php,v 1.1 2004/09/13 13:22:06 alberto Exp $
 */
class Dir extends Object {

	/**
	 * Directory path.
     * @access private
	 * @var string
	 */
    var $_root;
    
	/**
	 * Directory handle.
     * @access private
	 * @var mixed
	 */
    var $_handle;
    
	/**
	 * Information about the directory - size, creation date etc .
     * @access private
	 * @var array
	 */
    var $_status;

    /**
     * Dir class constructor.
     *
     * Creates the new instance of Dir class and sets up basic properties.
     *
     * @access public
     * @param string $root the path to directory, defaults to "./".
     * @return void
     */
    function Dir($root='./') {
        Object::Object();
        $slash = substr($root, -1);
        if (ord($slash) != 47) {
            $root = $root . chr(47);
        }
        $this->_root = $root;
        $this->_handle = null;
        $this->_status = array();
    }

    /**
     * Gets the directory path.
     *
     * Returns the directory path or empty string if the path has not been set
     * in object constructor.
     *
     * @access public
     * @return string
     */
    function getRoot() {
        return $this->_root;
    }

    /**
     * Gets the directory handle.
     *
     * Returns resource handle if the directory is opened, null otherwise.
     *
     * @access public
     * @return mixed
     */
    function getHandle() {
        return $this->_handle;
    }

    /**
     * Gets the user ID of the owner of the directory.
     *
     * If called when the directory is closed it returns error object,
     * otherwise it returns the user ID of the owner of the directory in
     * numerical format.
     *
     * @access public
     * @return mixed
     */
    function getOwner() {
        if (isset($this->_status[4])) {
            return $this->_status[4];
        }
        return new Error('getowner(): Directory has not been opened.');
    }

    /**
     * Gets the group ID of the owner of the directory.
     *
     * If called when the directory is closed it returns error object,
     * otherwise it returns the group ID of the owner of the directory in
     * numerical format.
     *
     * @access public
     * @return mixed
     */
    function getGroup() {
        if (isset($this->_status[5])) {
            return $this->_status[5];
        }
        return new Error('getgroup(): Directory has not been opened.');
    }

    /**
     * Opens the directory.
     *
     * If called when the the directory is already opened, it returns error
     * object otherwise it returns true.
     *
     * @access public
     * @return mixed
     */
    function open() {
        if (!is_resource($this->_handle)) {
            if (file_exists($this->_root) && is_dir($this->_root)) {
                $this->_handle = @opendir($this->_root);
                if (is_resource($this->_handle)) {
                    $this->_status = @stat($this->_root);
                    rewinddir($this->_handle);
                } else {
                    return new Error('open(): Directory could not be opened.');
                }
            } else {
                return new Error('open(): Specified invalid directory name.');
            }
        }  else {
            return new Error('open(): Directory is already opened.');
        }
        return true;
    }

    /**
     * Closes the directory.
     *
     * If called when the directory is closed or it couldn't be closed,
     * it returns error object, otherwise it returns true.
     *
     * @access public
     * @return mixed
     */
    function close() {
        if (is_resource($this->_handle)) {
            @closedir($this->_handle);
            if (is_resource($this->_handle)) {
                $this->_handle = null;
                $this->_status = array();
            } else {
	        
                return new Error('close(): Directory could not be closed.');
            }
        } else {
            return new Error('close(): Directory has not been opened.');
        }
        return true;
    }

    /**
     * Read list of files and subdirectories.
     *
     * If called when the directory is closed, it returns error object
     * otherwise it returns array with file and direcotry objects contained in
     * the directory. If the $asc param is omited the list is sorted in
     * ascending order.
     *
     * @access public
     * @return mixed
     */
    function read($asc=true) {
        global $dir_sortOrder;
        global $file_sortOrder;
        if (is_resource($this->_handle)) {
            $ditems = array();
            $fitems = array();
            while ($item = @readdir($this->_handle)) {
                if (is_dir($this->_root . $item) && $item != '.' && $item != '..') {
                    $ditems[] =& new Dir($this->_root . $item);
                } elseif (is_file($this->_root . $item)) {
                    $fitems[] =& new File($this->_root . $item);
                }
            }
            rewinddir($this->_handle);
            $dorder = $dir_sortOrder;
            $forder = $file_sortOrder;
            if ($asc) {
                $dir_sortOrder = 'asc';
                $file_sortOrder = 'asc';
            } else {
                $dir_sortOrder = 'dsc';
                $file_sortOrder = 'dsc';
            }
            usort($ditems, 'dir_cmp');
            usort($fitems, 'file_cmp');
            $dir_sortOrder = $dorder;
            $file_sortOrder = $forder;
        } else {
            return new Error('read(): Directory has not been opened.');
        }
        return array_merge($ditems, $fitems) ;
    }

    /**
     * Read list of subdirectories.
     *
     * If called when the directory is closed, it returns error object
     * otherwise it returns array with direcotry objects contained in the
     * directory. If the $asc param is omited the list is sorted in ascending
     * order.
     *
     * @access public
     * @return mixed
     */
    function readDirs($asc=true) {
        global $dir_sortOrder;
        if (is_resource($this->_handle)) {
            $result = array();
            while ($item = @readdir($this->_handle)) {
                if (is_dir($this->_root . $item) && $item != '.' && $item != '..') {
                    $result[] =& new Dir($this->_root . $item);
                }
            }
            rewinddir($this->_handle);
            $dorder = $dir_sortOrder;
            if ($asc) {
                $dir_sortOrder = 'asc';
            } else {
                $dir_sortOrder = 'dsc';
            }
            usort($result, 'dir_cmp');
            $dir_sortOrder = $dorder;
        } else {
            return new Error('readdirs(): Directory has not been opened.');
        }
        return $result;
    }

    /**
     * Read list of files.
     *
     * If called when the directory is closed, it returns error object
     * otherwise it returns array with file objects contained in the
     * directory. If the $asc param is omited the list is sorted in ascending
     * order.
     *
     * @access public
     * @return mixed
     */
    function readFiles($asc=true) {
        global $file_sortOrder;
        if (is_resource($this->_handle)) {
            $result = array();
            while ($item = @readdir($this->_handle)) {
                if (is_file($this->_root . $item)) {
                    $result[] =& new File($this->_root . $item);
                }
            }
            rewinddir($this->_handle);
            $forder = $file_sortOrder;
            if ($asc) {
                $file_sortOrder = 'asc';
            } else {
                $file_sortOrder = 'dsc';
            }
            usort($result, 'file_cmp');
            $file_sortOrder = $forder;
        } else {
            return new Error('readfiles(): Directory has not been opened.');
        }
        return $result;
    }

    /**
     * Retrieves the number of subdirectories.
     *
     * If called when the directory is not opened, it returns error object
     * otherwise it returns number of subdirectories.
     *
     * @access public
     * @return mixed
     */
    function dirCount() {
        if (is_resource($this->_handle)) {
            $result = 0;
            while ($item = @readdir($this->_handle)) {
                if (is_dir($this->_root . $item) && $item != '.' && $item != '..') {
                    $result++;
                }
            }
            rewinddir($this->_handle);
        } else {
            return new Error('dircount(): Directory has not been opened.');
        }
        return $result;
    }

    /**
     * Retrieves the list of subdirectory names.
     *
     * If called when the directory is not opened, it returns error object
     * otherwise it returns array with subdirectory names contained in the directory.
     *
     * @access public
     * @return mixed
     */
    function dirNames() {
        if (is_resource($this->_handle)) {
            $result = array();
            while ($item = @readdir($this->_handle)) {
                if (is_dir($this->_root . $item) && $item != '.' && $item != '..') {
                    $result[] = $item;
                }
            }
            rewinddir($this->_handle);
        } else {
            return new Error('dirnames(): Directory has not been opened.');
        }
        return $result;
    }

    /**
     * Retrieves the number of files.
     *
     * If called when the directory is not opened, it returns error object
     * otherwise it returns number of files.
     *
     * @access public
     * @return mixed
     */
    function fileCount() {
        if (is_resource($this->_handle)) {
            $result = 0;
            while ($item = @readdir($this->_handle)) {
                if (is_file($this->_root . $item)) {
                    $result++;
                }
            }
            rewinddir($this->_handle);
        } else {
            return new Error('filecount(): Directory has not been opened.');
        }
        return $result;
    }

    /**
     * Retrieves the list of file names.
     *
     * If called when the directory is not opened, it returns error object
     * otherwise it returns array with file names contained in the directory.
     *
     * @access public
     * @return mixed
     */
    function fileNames() {
        if (is_resource($this->_handle)) {
            $result = array();
            while ($item = @readdir($this->_handle)) {
                if (is_file($this->_root . $item)) {
                    $result[] = $item;
                }
            }
            rewinddir($this->_handle);
        } else {
            return new Error('dirnames(): Directory has not been opened.');
        }
        return $result;
    }
    
    /**
     * Attempts to remove the directory.
     *
     * If called when the directory is already opened, it returns error object
     * otherwise it returns true on success or error object with an error
     * message on any kind of failure.
     *
     * @access public
     * @return mixed
     */
    function delete() {
         if (!is_resource($this->_handle)) {
            if (file_exists($this->_root) && is_dir($this->_root)) {
                if (!@rmdir($this->_root)) {
                    return new Error('delete(): Directory could not be deleted.');
                }
            } else {
                return new Error('delete(): Directory does not exists.');
            }
        } else {
            return new Error('delete(): Directory is already opened.');
        }
        return true;
    }
    
    /**
     * Checks whether a directory exists.
     *
     * Returns true if the directory exists, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function exists() {
        return (file_exists($this->_root) && is_dir($this->_root));
    }
    
    /**
     * Attempts to create the subdirectory.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $path the path where to create the subdirectory.
     * @param int $mode the access permissions, defaults to 0755.
     * @return mixed
     */
    function create($path, $mode=0755) {
        if (strlen($path)) {
            $path = $this->_root . $path;
            if (!file_exists($path)) {
                if (!@mkdir($path, $mode)) {
                    return new Error('create(): Directory could not be created.');
                }
            } else {
                return new Error('create(): Directory already exists.');
            }
        } else {
            return new Error('create(): Specified directory path is empty.');
        }
        return true;
    }
    
    /**
     * Attempts to rename the subdirectory or file.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $path the path to the subdirectory or file.
     * @return mixed
     */
    function rename($path) {
        if (!is_resource($this->_handle)) {
            $path = dirname($this->_root) . '/' . $path;
            if (!file_exists($path)) {
                if (!@rename($this->_root, $path)) {
                    return new Error('rename(): Directory could not be removed.');
                }
            } else {
                return new Error('rename(): Directory already exists.');
            }
        } else {
            return new Error('rename(): Directory is already opened.');
        }
        return true;
    }

    /**
     * Attempts to remove the subdirectory or file.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $path the path to the subdirectory or file.
     * @return mixed
     */
    function remove($path) {
        if (strlen($path)) {
            $path = $this->_root . $path;
            if (file_exists($path)) {
                if (!@rmdir($path)) {
                    return new Error('remove(): Directory could not be removed.');
                }
            } else {
                return new Error('remove(): Directory does not exists.');
            }
        } else {
            return new Error('remove(): Specified directory path is empty.');
        }
        return true;
    }

    /**
     * Finds whether the directory is opened.
     *
     * Returns true if the directory is opened, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isOpened() {
        return is_resource($this->_handle);
    }

    /**
     * Finds whether the directory is readable.
     *
     * Returns true if the directory exists and is readable, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isReadable() {
        return is_readable($this->_root);
    }

    /**
     * Finds whether the directory is writeable.
     *
     * Returns true if the directory exists and is writeable, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isWriteable() {
        return is_writeable($this->_root);
    }

}

/**
 * Performs directories comparison.
 *
 * This function is used internally by {@link Dir::read()},
 * {@link Dir::readDirs()}  methods and it shouldn't be used outside these
 * methods. Returns < 0 if $obj1->_root is less than $obj2->_root; > 0
 * if $obj1->_root is greater than $obj2->_root, and 0 if they are equal.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access private
 * @param string $obj1 the Dir object.
 * @param string $obj2 the Dir object.
 * @return int
 */
function dir_cmp($obj1, $obj2) {
    global $dir_sortOrder;
    $str1 = strtolower($obj1->_root);
    $str2 = strtolower($obj2->_root);
    if ($dir_sortOrder != 'dsc') {
        return strcmp($str1, $str2);
    } else {
        if ($str1 == $str2) {
            return 0;
        }
        if ($str1 > $str2) {
            return -1;
        } else {
            return 1;
        }
    }
}

?>
