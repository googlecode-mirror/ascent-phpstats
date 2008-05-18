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
 * The sort order used during file sorting operations.
 *
 * This variable shouldn't be modified. It is used internally by
 * {@link Dir::read()}, {@link Dir::readFiles()} methods.
 *
 * @global string $file_sortOrder
 *
 */
$file_sortOrder = 'asc';

/**
 * The sorting field used during file sorting operations.
 *
 * This variable shouldn't be modified. It is used internally by
 * {@link Dir::read()}, {@link Dir::readFiles()} methods. Files can be sorted
 * according to its names, size and modification date.
 *
 * @global string $file_sortField
 *
 */
$file_sortField = 'name';

/**
 * File manipulation class.
 *
 * File class is a wrapper used for basic file operations just like reading,
 * writing, coping, etc.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @package io
 * @version $Id: file.inc.php,v 1.1 2004/09/13 13:22:06 alberto Exp $
 */
class File extends Object {
	/**
	 * Temporary file name when working with copy of the file.
     * @access private
	 * @var string
	 */
    var $_temp;
    
	/**
	 * Path to file or file name.
     * @access private
	 * @var string
	 */
    var $_name;
    
	/**
	 * File access mode.
     * @access private
	 * @var string
	 */
    var $_mode;

	/**
	 * File handle.
     * @access private
	 * @var mixed
	 */
    var $_handle;

	/**
	 * Indicates whether the file is locked or not.
     * @access private
	 * @var boolean
	 */
    var $_locked;
    
	/**
	 * Indicates whether works on orginal file or its copy.
     * @access private
	 * @var boolean
	 */
    var $_backup;

	/**
	 * Number of bytes to read and write at IO operations.
     * @access private
	 * @var int
	 */
    var $_buffer;

    /**
     * File class constructor.
     *
     * Creates the new instance of File class and sets up basic properties.
     *
     * @access public
     * @param string $name the path to file or file name, defaults to "".
     * @param string $mode the access mode to file stream, defaults to "r".
     * @param boolean $backup indicates whether works with orginal file or its copy.
     * @param boolean $buffer the number of bytes to read and write at IO operations.
     * @return void
     */
    function File($name='', $mode='r', $backup=false, $buffer=1024) {
        Object::Object();
        $this->_temp = '';
        $this->_name = $name;
        $this->_mode = $mode;
        $this->_handle = null;
        $this->_locked = false;
        $this->_backup = $backup;
        $this->_buffer = $buffer;
    }

    /**
     * Gets path to file or file name.
     *
     * Returns path to file or file name or empty string if it has not been set
     * in object constructor.
     *
     * @access public
     * @return string
     */
    function getName() {
        return $this->_name;
    }

    /**
     * Gets the file access mode.
     *
     * Returns file access mode.
     *
     * @access public
     * @return mixed
     */
    function getMode() {
        return $this->_mode;
    }

    /**
     * Gets the file handle.
     *
     * Returns resource handle if the file is opened, null otherwise.
     *
     * @access public
     * @return mixed
     */
    function getHandle() {
        return $this->_handle;
    }

    /**
     * Gets the file buffer size.
     *
     * Returns number of bytes used at file IO operations.
     *
     * @access public
     * @return int
     */
    function getBuffer() {
        return $this->_buffer;
    }

    /**
     * Gets the size of the file in bytes.
     *
     * Returns the size of the file in bytes if the file exists, error object otherwise.
     *
     * @access public
     * @return mixed
     */
    function getSize() {
        if (file_exists($this->_name)) {
            return @filesize($this->_name);
        }
        return new Error('getsize(): File does not exists.');
    }

    /**
     * Gets the user ID of the owner of the file.
     *
     * If called when the file does not exists it returns error object, otherwise
     * it returns the user ID of the owner of the file in numerical format.
     *
     * @access public
     * @return mixed
     */
    function getOwner() {
        if (file_exists($this->_name)) {
            return @fileowner($this->_name);
        }
        return new Error('getowner(): File does not exists.');
    }

    /**
     * Gets the group ID of the owner of the file.
     *
     * If called when the file does not exists it returns error object, otherwise
     * it returns the group ID of the owner of the file in numerical format.
     *
     * @access public
     * @return mixed
     */
    function getGroup() {
        if (file_exists($this->_name)) {
            return @filegroup($this->_name);
        }
        return new Error('getgroup(): File does not exists.');
    }

    /**
     * Gets the time the file was last accessed.
     *
     * If called when the file does not exists it returns error object, otherwise
     * it returns the time the file was last accessed. The time is returned as
     * a Unix timestamp.
     *
     * @access public
     * @return int
     */
    function getATime() {
        if (file_exists($this->_name)) {
            return @fileatime($this->_name);
        }
        return new Error('getatime(): File does not exists.');
    }

    /**
     * Gets the time the file was last changed.
     *
     * If called when the file does not exists it returns error object, otherwise
     * it returns the time the file was last changed. The time is returned as
     * a Unix timestamp.
     *
     * @access public
     * @return int
     */
    function getCTime() {
        if (file_exists($this->_name)) {
            return @filectime($this->_name);
        }
        return new Error('getctime(): File does not exists.');
    }

    /**
     * Gets the time the file was last modified.
     *
     * If called when the file does not exists it returns error object, otherwise
     * it returns the time the file was last modified. The time is returned as
     * a Unix timestamp.
     *
     * @access public
     * @return int
     */
    function getMTime() {
        if (file_exists($this->_name)) {
            return @filemtime($this->_name);
        }
        return new Error('getmtime(): File does not exists.');
    }

    /**
     * Sets the file access mode.
     *
     * The mode specifies the type of access you require to the file stream.
     * If called when the file is opened, it returns error object otherwise it
     * returns true on success.
     *
     * @access public
     * @return mixed
     */
    function setMode($mode) {
        if (strlen($mode)) {
            if (empty($this->_handle)) {
                $this->_mode = $mode;
            } else {
                return new Error('setmode(): File is already opened.');
            }
        } else {
            return new Error('setmode(): File mode is not specified.');
        }
        return true;
    }

    /**
     * Sets the file buffer size.
     *
     * Returns true on success or a error object if the buffer size is not a
     * valid integer value.
     *
     * @access public
     * @return mixed
     */
    function setBuffer($buffer) {
        if (is_integer($buffer)) {
            $this->_buffer = $buffer;
        } else {
            return new Error('setbuffer(): Invalid file buffer size.');
        }
        return true;
    }

    /**
     * Attempts to copy the file.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure. File is copied to specified destination and access
     * permissions are changed according to the mode param. Note that mode is
     * not automatically assumed to be an octal value. To ensure the expected
     * operation, you need to prefix mode with a zero (0):
     *
     * @access public
     * @param string $destination the new file name.
     * @param int $mode the access permissions, defaults to 0644.
     * @return mixed
     */
    function copy($destination, $mode=0644) {
        if (strlen($destination)) {
            if (file_exists($this->_name) && is_file($this->_name)) {
                if (!@copy($this->_name, $destination)) {
                    return new Error('copy(): File could not be copied.');
                }
                if (!@chmod($destination, $mode)) {
                    return new Error('copy(): Could not change file mode.');
                }
            } else {
                return new Error('copy(): File is not regular file or does not exists.');
            }
        } else {
            return new Error('copy(): Specified file destination is empty.');
        }
        return true;
    }
    
    /**
     * Attempts to change the mode of the file.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure. Note that mode is not automatically assumed to be an
     * octal value. To ensure the expected operation, you need to prefix mode
     * with a zero (0):
     *
     * @access public
     * @param int $mode the access permissions, defaults to 0644.
     * @return mixed
     */
    function chmod($mode=0644) {
         if (file_exists($this->_name) && is_file($this->_name)) {
             if (!@chmod($this->_name, $mode)) {
                 return new Error('chmod(): Could not change file mode.');
             }
         } else {
             return new Error('chmod(): File is not regular file or does not exists.');
         }
         return true;
    }

    /**
     * Attempts to delete the file.
     *
     * If called when the file is already opened, it returns error object
     * otherwise it returns true on success or error object with an error
     * message on any kind of failure.
     *
     * @access public
     * @param string $path the path where to create the subdirectory.
     * @param int $mode the access permissions, defaults to 0755.
     * @return mixed
     */
    function delete() {
        if (!is_resource($this->_handle)) {
            if (file_exists($this->_name) && is_file($this->_name)) {
                if (!@unlink($this->_name)) {
                    return new Error('delete(): File could not be deleted.');
                }
            } else {
                return new Error('delete(): File is not regular file or does not exists.');
            }
        } else {
            return new Error('delete(): File is already opened.');
        }
        return true;
    }

    /**
     * Checks whether a file exists.
     *
     * Returns true if the file exists, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function exists() {
        return (file_exists($this->_name) && is_file($this->_name));
    }

    /**
     * Attempts to rename the file.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @param string $name the new file name.
     * @return mixed
     */
    function rename($name) {
        if (strlen($name)) {
            if (file_exists($this->_name) && is_file($this->_name)) {
                $name = dirname($this->_name) . '/' . $name;
                if (@rename($this->_name, $name)) {
                    $this->_name = $name;
                } else {
                    return new Error('rename(): File could not be renamed.');
                }
            } else {
                return new Error('rename(): File is not regular file or does not exists.');
            }
        } else {
            return new Error('rename(): File name is empty.');
        }
        return true;
    }

    /**
     * Opens the file.
     *
     * If called when the the file is already opened, it returns error
     * object otherwise it will try to open a file stream and returns true.
     * The file must be accessible to PHP, so you need to ensure that the file
     * access permissions allow this access. Type of access to the stream is
     * controled by the mode property.
     *
     * @access public
     * @return mixed
     */
    function open() {
        if (!is_resource($this->_handle)) {
            if (!$this->_backup) {
                $this->_handle = @fopen($this->_name, $this->_mode);
            } else {
                $this->_temp = tempnam('', 'php');
                if (file_exists($this->_name)) {
                    @copy($this->_name, $this->_temp);
                }
                $this->_handle = @fopen($this->_temp, $this->_mode);
            }
            if (empty($this->_handle)) {
                if (file_exists($this->_temp)) {
                    unlink($this->_temp);
                }
                return new Error('open(): File could not be opened.');
            }
        } else {
            return new Error('open(): File is already opened.');
        }
        return true;
    }

    /**
     * Closes the file.
     *
     * If called when the file is closed or it couldn't be closed,
     * it returns error object, otherwise it returns true.
     *
     * @access public
     * @return mixed
     */
    function close() {
        if (is_resource($this->_handle)) {
            if (@fclose($this->_handle)) {
                $this->_handle = null;
                $this->_locked = false;
                if ($this->_backup) {
                    if (!@copy($this->_temp, $this->_name)) {
                        return new Error('close(): Could not update file.');
                    }
                    unlink($this->_temp);
                }
            } else {
                return new Error('close(): File could not be closed.');
            }
        } else {
            return new Error('close(): File has not been opened.');
        }
        return true;
    }

    /**
     * Attempts to lock access to the file.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @return mixed
     */
    function lock() {
        if (is_resource($this->_handle)) {
           if (@flock($this->_handle, LOCK_EX)) {
                $this->_locked = true;
            } else {
                return new Error('lock(): File could not be locked.');
            }
        } else {
            return new Error('lock(): File has not been opened.');
        }
        return true;
    }

    /**
     * Attempts to unlock access to the file.
     *
     * Returns true on success or error object with an error message on any
     * kind of failure.
     *
     * @access public
     * @return mixed
     */
    function unlock() {
        if (is_resource($this->_handle)) {
            if (@flock($this->_handle, LOCK_UN)) {
                $this->_locked = false;
            } else {
                return new Error('unlock(): File could not be unlocked.');
            }
        } else {
            return new Error('unlock(): File has not been opened.');
        }
        return true;
    }

    /**
     * Read data from a file.
     *
     * Reads up to the file buffer size from the opened file. Reading stops when
     * the file buffer size have been read or end-of-file reached, whichever
     * comes first. If called when the file is closed, it returns error object
     * otherwise it returns fread() result.
     *
     * @access public
     * @return mixed
     */
    function read() {
        if (is_resource($this->_handle)) {
            if (is_readable($this->_name)) {
                $result = @fread($this->_handle, @filesize($this->_name));
            } else {
                return new Error('read(): Could not read from file.');
            }
        } else {
            return new Error('read(): File has not been opened.');
        }
        return $result;
    }

    /**
     * Write data to a file.
     *
     * Write data to a file. Writing will stop after file buffer
     * length bytes have been written or the end of string is reached, whichever
     * comes first. If called when the file is closed, it returns error object
     * otherwise it returns fwrite() result.
     *
     * @access public
     * @param string $string the data to write.
     * @return mixed
     */
   function write($string) {
        if (is_resource($this->_handle)) {
            if (is_writable($this->_name)) {
                $result = @fwrite($this->_handle, $string);
            } else {
                return new Error('write(): Could not write to file.');
            }
        } else {
            return new Error('write(): File has not been opened.');
        }
        return $result;
    }

    /**
     * Read a line of data from a file.
     *
     * Read until either the end of the file or a newline, whichever comes
     * first. If called when the file is closed, it returns error object
     * otherwise it returns all available data up to a newline, without
     * that newline, or until the end of the file as string.
     *
     * @access public
     * @return mixed
     */
    function readLine() {
        if (is_resource($this->_handle)) {
            if (is_readable($this->_name)) {
                $result = '';
                while (!feof($this->_handle)) {
                   $result .= fgets($this->_handle, $this->_buffer);
                   if (ereg("\r\n|\n", $result)) {
                       $result = ereg_replace("\r\n|\n", "", $result);
                       break;
                   }
                }
            } else {
                return new Error('readline(): Could not read from file.');
            }
        } else {
            return new Error('readline(): File has not been opened.');
        }
        return $result;
    }

    /**
     * Write a line of data to a file.
     *
     * Write a line of data to the opened file, followed by a trailing "\r\n".
     * If called when the file is closed, it returns error object
     * otherwise it returns fputs result.
     *
     * @access public
     * @param string $string the data to write.
     * @return mixed
     */
    function writeLine($string) {
        if (is_resource($this->_handle)) {
            if (is_writable($this->_name)) {
                $result = fputs($this->_handle, $string . "\r\n");
            } else {
                return new Error('writeline(): Could not write to file.');
            }
        } else {
            return new Error('writeline(): File has not been opened.');
        }
        return $result;
    }

    /**
     * Read buffer from a file.
     *
     * Reads up to the specified buffer size from the opened file. Reading
     * stops when the specified buffer size have been read or end-of-stream
     * reached, whichever comes first. If called when the file is closed,
     * it returns error object otherwise it returns fread() result.
     *
     * @access public
     * @param string $buffer the data to read.
     * @param int $length the buffer length, defaults to 0.
     * @return mixed
     */
    function readBuffer(&$buffer, $length=0) {
        if (is_resource($this->_handle)) {
            if (is_readable($this->_name)) {
                $buffer = '';
                if ($length > 0) {
                    $buffer = @fread($this->_handle, $length);
                } else {
                    $buffer = @fread($this->_handle, $this->_buffer);
                }
                $result = strlen($buffer);
            } else {
                return new Error('readbuffer(): Could not read from file.');
            }
        } else {
            return new Error('readbuffer(): File has not been opened.');
        }
        return $result;
    }

    /**
     * Write buffer to a file.
     *
     * Write data to a opened file. Writing will stop after specified buffer
     * length bytes have been written or the end of string is reached, whichever
     * comes first. If called when the file is closed, it returns error object
     * otherwise it returns fwrite() result.
     *
     * @access public
     * @param string $buffer the data to write.
     * @param int $length the buffer length, defaults to 0.
     * @return mixed
     */
   function writeBuffer(&$buffer, $length=0) {
        if (is_resource($this->_handle)) {
            if (is_writable($this->_name)) {
                if ($length > 0) {
                    $result = @fwrite($this->_handle, $buffer, $length);
                } else {
                    $result = @fwrite($this->_handle, $buffer, $this->_buffer);
                }
            } else {
                return new Error('writebuffer(): Could not write to file.');
            }
        } else {
            return new Error('writebuffer(): File has not been opened.');
        }
        return $result;
    }
    
    /**
     * Finds whether the file is at end-of-file or closed.
     *
     * Returns true if the file is closed or the file is at end-of-file,
     * false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isEof() {
        return is_null($this->_handle) || feof($this->_handle);
    }

    /**
     * Finds whether the file is a symbolic link.
     *
     * Returns true if the file exists and is a symbolic link, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isLink() {
        return is_link($this->_name);
    }

    /**
     * Finds whether the file is locked.
     *
     * Returns true if the file is locked, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isLocked() {
        return $this->_locked;
    }

    /**
     * Finds whether the file is opened.
     *
     * Returns true if the file is opened, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isOpened() {
        return is_resource($this->_handle);
    }

    /**
     * Finds whether the file was uploaded via HTTP POST.
     *
     * Returns true if the file was uploaded via HTTP POST, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isUploaded() {
        return is_uploaded_file($this->_name);
    }

    /**
     * Finds whether the file is readable.
     *
     * Returns true if the file exists and is readable, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isReadable() {
        return is_readable($this->_name);
    }

    /**
     * Finds whether the file is writeable.
     *
     * Returns true if the file exists and is writeable, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isWriteable() {
        return is_writeable($this->_name);
    }

    /**
     * Finds whether the file is executable.
     *
     * Returns true if the file exists and is executable, false otherwise.
     *
     * @access public
     * @return boolean
     */
    function isExecutable() {
        return is_executable($this->_name);
    }

}

/**
 * Performs files comparison.
 *
 * This function is used internally by {@link Dir::read()},
 * {@link Dir::readFiles()} methods and it shouldn't be
 * used outside these methods. Returns < 0 if $obj1->_root is less than
 * $obj2->_root; > 0 if $obj1->_root is greater than $obj2->_root, and 0 if
 * they are equal.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access private
 * @param string $obj1 the File object.
 * @param string $obj2 the File object.
 * @return int
 */
function file_cmp($obj1, $obj2) {
    global $file_sortOrder;
    global $file_sortField;
    switch($file_sortField) {
        case 'name':
            $field = 'getName';
            break;
        case 'size':
            $field = 'getSize';
            break;
        case 'date':
            $field = 'getMTime';
            break;
    }
    if(!isset($field))$field = 'getName';
    $str1 = strtolower($obj1->$field());
    $str2 = strtolower($obj2->$field());
    if ($str1 == $str2) {
        return 0;
    }
    if ($file_sortOrder != 'dsc') {
        if ($str1 < $str2) {
            return -1;
        } else {
            return 1;
        }
    } else {
        if ($str1 > $str2) {
            return -1;
        } else {
            return 1;
        }
    }
}

/**
 * Retrieves the file MIME media type and sub type.
 *
 * Returns the array containing MIME media type and sub type according to file
 * extension.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @param string $filename the file name.
 * @return array
 */
function file_getMime($filename) {
    $extension = strrchr($filename, '.');
    switch (strtolower($extension)) {
        case '.avi':
            $result['mtype'] = 'video';
            $result['stype'] = 'x-msvideo';
            break;
        case '.bmp':
            $result['mtype'] = 'image';
            $result['stype'] = 'bmp';
            break;
        case '.gif' :
            $result['mtype'] = 'image';
            $result['stype'] = 'gif';
            break;
        case '.jpg' :
            $result['mtype'] = 'image';
            $result['stype'] = 'gif';
            break;
        case '.jpeg':
            $result['mtype'] = 'image';
            $result['stype'] = 'jpeg';
            break;
        case '.js':
            $result['mtype'] = 'application';
            $result['stype'] = 'x-javascript';
            break;
        case '.doc':
            $result['mtype'] = 'application';
            $result['stype'] = 'msword';
            break;
        case '.pl':
            $result['mtype'] = 'text';
            $result['stype'] = 'plain';
            break;
        case '.css':
            $result['mtype'] = 'text';
            $result['stype'] = 'css';
            break;
        case '.htm':
            $result['mtype'] = 'text';
            $result['stype'] = 'html';
            break;
        case '.html':
            $result['mtype'] = 'text';
            $result['stype'] = 'html';
            break;
        case '.mid':
            $result['mtype'] = 'audio';
            $result['stype'] = 'midi';
            break;
        case '.mov':
            $result['mtype'] = 'video';
            $result['stype'] = 'quicktime';
            break;
        case '.mp3':
            $result['mtype'] = 'audio';
            $result['stype'] = 'mpeg';
            break;
        case '.mpeg':
            $result['mtype'] = 'video';
            $result['stype'] = 'mpeg';
            break;
        case '.mpg':
            $result['mtype'] = 'video';
            $result['stype'] = 'mpeg';
            break;
        case '.php':
            $result['mtype'] = 'application';
            $result['stype'] = 'x-httpd-php';
            break;
        case '.php3':
            $result['mtype'] = 'application';
            $result['stype'] = 'x-httpd-php';
            break;
        case '.phps':
            $result['mtype'] = 'application';
            $result['stype'] = 'x-httpd-php-source';
            break;
        case '.pdf':
            $result['mtype'] = 'application';
            $result['stype'] = 'pdf';
            break;
        case '.txt':
            $result['mtype'] = 'text';
            $result['stype'] = 'plain';
            break;
        case '.wav':
            $result['mtype'] = 'audio';
            $result['stype'] = 'x-wav';
            break;
        case '.ram':
            $result['mtype'] = 'audio';
            $result['stype'] = 'x-pn-realaudio';
            break;
        case '.zip':
            $result['mtype'] = 'application';
            $result['stype'] = 'zip';
            break;
        case '.tar':
            $result['mtype'] = 'application';
            $result['stype'] = 'x-tar';
            break;
        case '.gz':
            $result['mtype'] = 'application';
            $result['stype'] = 'x-gzip';
            break;
        case '.tgz':
            $result['mtype'] = 'application';
            $result['stype'] = 'x-tar';
            break;
        default:
            $result['mtype'] = 'application';
            $result['stype'] = 'octetstream';
    }
    return $result;
}

/**
 * Syntax highlighting of a file.
 *
 * Returns a syntax higlighted version of the code contained in file using the
 * colors defined in the built-in syntax highlighter for PHP. If the file could
 * not be readed the empty string is returned.
 *
 * @author Alberto Ferrer <albertof@barrahome.org>
 * @access public
 * @param string $filename the file name.
 * @return string
 */
function file_highlight($filename) {
   $opened = is_integer(ob_get_length());
   if ($opened == true) {
       ob_end_clean();
   }
   ob_start();
   if (highlight_file($filename)) {
       $result = ob_get_contents();
       ob_end_clean();
       if ($opened) {
           ob_start();
       }
       return $result;
   }
   if ($opened == false) {
       ob_end_clean();
   }
   return '';
}

?>
