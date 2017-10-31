<?php

    /**
     * STC - Simplify The Code
     *
     * An open source application development framework for PHP
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2015 - 2017, Alien Technologies
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     *
     * @package     STC
     * @author      Nana Axel <ax.lnana@outlook.com>
     * @copyright   Copyright (c) 2015 - 2017, Alien Technologies
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @filesource
     */

    defined('BASEPATH') OR exit('No direct script access allowed');

    /**
     * FileSystem Class
     *
     * @package     STC
     * @subpackage  Utilities
     * @category    FileSystem
     * @author      Nana Axel <ax.lnana@outlook.com>
     */
    class STC_FileSystem
    {
        /**
         * The temp directory
         *
         * @const string
         */
        const TMP_DIR = FCPATH . '/tmp';

        /**
         * Choose to output the real filepath
         *
         * @const int
         */
        const REAL_PATH = 1;

        /**
         * Choose to output the internal filepath
         *
         * @const int
         */
        const INTERNAL_PATH = 2;

        /**
         * Choose to output the external filepath
         *
         * @const int
         */
        const EXTERNAL_PATH = 3;

        /**
         * Choose to output the external filepath
         *
         * @const int
         */
        const FILESYSTEM_PATH = 4;

        /**
         * The FileSystem root path
         *
         * @var string
         * @access protected
         */
        protected $rootpath = '';

        /**
         * The current working directory
         *
         * @var string
         * @access protected
         */
        protected $workingDir = '';

        /**
         * Aliasies
         *
         * @var string
         * @access protected
         */
        protected $aliases = array();

        /**
         * Class constructor
         *
         * Set de default root path,
         * the default working directory,
         * and defaults aliases.
         *
         * @return void
         */
        public function __construct($rootpath = FALSE) {
            if ($rootpath === FALSE) {
                $this->setRootpath(FCPATH);
            }
            else {
                $this->setRootpath($this->cleanPath($rootpath));
            }

            $this->setWorkingDir('/');

            return ;
        }

        /**
         * Gets the value of rootpath
         *
         * @return string
         */
        public function rootpath() {
            return $this->rootpath;
        }

        public function setRootpath($rootpath) {
            $this->rootpath = $this->cleanPath($rootpath);
        }

        /**
         * Set the current working directory
         *
         * @param string The path to the directory
         *
         * @return void
         */
        public function setWorkingDir($workingDir) {
            $this->workingDir = $workingDir;
        }

        /**
         * Get the current working directory
         *
         * @return string
         */
        public function workingDir() {
            return $this->workingDir;
        }

        /**
         * Get the aliases
         *
         * @return array
         */
        public function getaliases() {
            return $this->aliases;
        }

        /**
         * Create a new alias
         *
         * @param string $key The key of the alias
         * @param string $val The value of the alias
         *
         * @return void
         */
        public function newAlias($key, $val) {
            if (substr($val, -1) == '/') {
                $val = substr($val, 0, -1);
            }

            $this->aliases[$key] = $val;

            return ;
        }

        /**
         * Create a new file
         *
         * @param string  $path         The path of the new file
         * @param boolean $createParent Define if we have to create parent directories
         *
         * @throws RuntimeException
         *
         * @return boolean
         */
        public function mkfile($path, $createParent = FALSE) {
            $parentDir = $this->dirname($path);

            if (!$this->exists($parentDir)) {
                if ($createParent) {
                    $this->mkdir($parentDir, TRUE);
                }
                else {
                    throw new \RuntimeException("Cannot create file \"{$path}\" (parent directory \"{$parentDir}\" doesn't exist)");
                }
            }

            $internalPath = $this->toInternalPath($path);

            if (touch($internalPath) === FALSE) {
                throw new \RuntimeException("Cannot create file \"{$path}\"");
            }
            else {
                chmod($internalPath, 0777);
                return TRUE;
            }
        }

        /**
         * Read the file's contents
         *
         * @param string $path The path to the file
         *
         * @throws RuntimeException
         *
         * @return string
         */
        public function read($path) {
            $internalPath = $this->toInternalPath($path);

            if (!$this->isRemote($path) && !is_readable($internalPath)) {
                throw $this->accessDeniedException($path, 'read');
            }

            $contents = file_get_contents($internalPath);

            if ($contents === FALSE) {
                throw new \RuntimeException("Cannot read the file \"{$path}\"");
            }

            return $contents;
        }

        /**
         * Write in the file
         *
         * @param string  $path   The file to write in
         * @param string  $data   The new contents of the file
         * @param boolean $append If the datas have to be appended in the file
         *
         * @throws RuntimeException
         *
         * @return boolean
         */
        public function write($path, $data, $append = FALSE) {
            $internalPath = $this->toInternalPath($path);
            $applyChmod   = FALSE;

            if ($append === TRUE) {
                $contents = $this->read($path);
                $data = $contents.$data;
                if (file_put_contents($internalPath, $data) !== FALSE) {
                    return TRUE;
                }
                else {
                    throw new \RuntimeException("Cannot write in the file \"{$path}\"");
                }
            }
            else {
                if ($this->exists($path)) {
                    if (!$this->isRemote($path) && !is_writable($internalPath)) {
                        throw $this->accessDeniedException($path, 'write');
                    }
                }
                else {
                    $applyChmod = TRUE;
                    $this->mkfile($path, TRUE);
                }

                if (file_put_contents($internalPath, $data) !== FALSE) {
                    if ($applyChmod) {
                        chmod($internalPath, 0777);
                    }
                    return TRUE;
                }
                else {
                    throw new \RuntimeException("Cannot write in the file \"{$path}\"");
                }
            }
        }

        /**
         * Delete the file
         *
         * @param string $path      The path to the file to delete
         * @param boolean   $recursive Define if we have to delete all subfiles
         *
         * @throws RuntimeException
         *
         * @return boolean
         */
        public function delete($path, $recursive = FALSE) {
            $internalPath = $this->toInternalPath($path);

            if ($this->exists($path)) {
                if ($this->isDir($path)) {
                    if ($recursive === TRUE) {
                        $subfiles = $this->readDir($path);
                        foreach ($subfiles as $fileToDelete) {
                            $this->delete($fileToDelete, $recursive);
                        }
                    }
                    if (rmdir($internalPath) === FALSE) {
                        throw new \RuntimeException("Cannot delete directory \"{$path}\".");
                    }
                    else {
                        return TRUE;
                    }
                }
                else {
                    if (unlink($internalPath) === FALSE) {
                        throw new \RuntimeException("Cannot delete file \"{$path}\".");
                    }
                    else {
                        return TRUE;
                    }
                }
            }
            else {
                throw new \RuntimeException("The file {$path} doesn't exist.");
            }
        }

        /**
         * Move the file
         *
         * @param string $path     The current path of the file
         * @param string $new_path The new path of the file
         *
         * @throws RuntimeException
         *
         * @return boolean
         */
        public function move($path, $new_path) {
            if ($this->isDir($new_path) && !$this->isDir($path)) {
                $new_path = make_path(array($new_path, $this->basename($path)));
            }

            $destDirname = $this->dirname($new_path);

            if (!$this->exists($destDirname)) {
                $this->mkdir($destDirname, TRUE);
            }

            $destInternalPath   = $this->toInternalPath($new_path);
            $sourceInternalPath = $this->toInternalPath($path);

            if (!rename($sourceInternalPath, $destInternalPath)) {
                throw new \RuntimeException("Cannot move file from \"{$path}\" to \"{$new_path}\"");
            }
            else {
                return TRUE;
            }
        }

        /**
         * Rename the file
         *
         * @param string $path     The current path of the file
         * @param string $new_path The new path of the file
         *
         * @throws RuntimeException
         *
         * @return boolean
         */
        public function rename($path, $new_path) {
            return $this->move($path, $new_path);
        }

        /**
         * Copy a file
         *
         * @param string $path     The path of the file to copy
         * @param string $new_path The path of the destination
         *
         * @throws RuntimeException
         *
         * @return boolean
         */
        public function copy($path, $new_path) {
            if ($this->isDir($new_path) && !$this->isDir($path)) {
                $new_path = $this->cleanPath(make_path(array($new_path, $this->basename($path))));
            }

            $destDirname = $this->dirname($new_path);

            if (!$this->exists($destDirname)) {
                throw new \RuntimeException("Cannot copy file from \"{$path}\" to \"{$new_path}\" : destination directory \"{$destDirname}\" doesn't exist");
            }

            $destInternalPath   = $this->toInternalPath($new_path);
            $sourceInternalPath = $this->toInternalPath($path);

            if ($this->isDir($sourceInternalPath)) {
                if (!$this->exists($new_path)) {
                    $this->mkdir($new_path);
                }
                $subfiles = $this->readDir($path);
                foreach ($subfiles as $fileToCopyName => $fileToCopyPath) {
                    return $this->copy("{$path}/{$fileToCopyName}", "{$new_path}/{$fileToCopyName}");
                }
            }
            else {
                if (copy($sourceInternalPath, $destInternalPath) === FALSE) {
                    throw new \RuntimeException("Cannot copy file from \"{$path}\" to \"{$new_path}\"");
                }
                else {
                    return TRUE;
                }
            }

        }

        /**
         * Upload a file.
         *
         * @param object $file The file to upload.
         * @param string $path The new file path.
         *
         * @return boolean If the file is sucessfully uploaded.
         */
        public function upload($file, $path) {
            $internalPath = $this->toInternalPath($path);

            if ($this->isDir($internalPath)) {
                $filename = $file['name'];
            }
            else {
                $filename = $this->basename($internalPath);
            }
        }

        /**
         * Create a new temporary file.
         *
         * @return string The temporary file path.
         */
        public function tmpfile() {
            $tmpDir = $this->toInternalPath(self::TMP_DIR);

            if (!$this->isDir($tmpDir)) {
                $this->mkdir($tmpDir, TRUE);
            }

            $tmpFile = tempnam($tmpDir, 'tmp');

            $fileManager = $this;
            $currentDir  = getcwd();

            register_shutdown_function(function() use ($tmpFile, $fileManager, $currentDir) {
                chdir($currentDir);

                if ($fileManager->exists($tmpFile)) {
                    $fileManager->delete($tmpFile, TRUE);
                }
            });

            return $tmpFile;
        }

        /**
         * Create a new directory
         *
         * @param string $path      The path of the new directory
         * @param boolean   $recursive Define if we have to create all parent directories
         *
         * @throws RuntimeException
         *
         * @return boolean
         */
        public function mkdir($path, $recursive = FALSE) {
            $internalPath = $this->toInternalPath($path);

            if (mkdir($internalPath, 0777, $recursive) === FALSE) {
                throw new \RuntimeException("Cannot create directory {$path}");
            }
            else {
                chmod($internalPath, 0777);
                return TRUE;
            }
        }

        /**
         * Check if the file exists
         *
         * @param string $path The path to the file
         *
         * @return boolean
         */
        public function exists($path) {
            return file_exists($this->toInternalPath($path));
        }

        /**
         * Check if the file is a directory
         *
         * @param string $path The path to the file
         *
         * @return boolean
         */
        public function isDir($path) {
            return is_dir($this->toInternalPath($path));
        }

        /**
         * Get the file's last modification time
         *
         * @param string $path The path to the file
         *
         * @return int
         */
        public function lastModTime($path) {
            return filemtime($this->toInternalPath($path));
        }

        /**
         * Get the file's last acces time
         *
         * @param string $path The path to the file
         *
         * @return int
         */
        public function lastAccessTime($path) {
            return fileatime($this->toInternalPath($path));
        }

        /**
         * Get the file's basename
         *
         * @param string $path The path to the file
         *
         * @return string
         */
        public function basename($path) {
            return $this->pathInfo($path, PATHINFO_BASENAME);
        }

        /**
         * Get the file's extension
         *
         * @param string $path the path to the file
         *
         * @return string
         */
        public function extension($path) {
            return $this->pathInfo($path, PATHINFO_EXTENSION);
        }

        /**
         * Get the file's name without extension
         *
         * @param string $path the path to the file
         *
         * @return string
         */
        public function filename($path) {
            return $this->pathInfo($path, PATHINFO_FILENAME);
        }

        /**
         * Get the parent directory of a file
         *
         * @param string $path The path to the file
         *
         * @return string
         */
        public function dirname($path) {
            return $this->pathInfo($path, PATHINFO_DIRNAME);
        }

        /**
         * Get the file's size
         *
         * @param string $path The path to the file
         *
         * @return int
         */
        public function size($path) {
            if ($this->isDir($path)) {
                $totalSize = 0;
                $files = $this->readDir($path);

                foreach ($files as $filepath) {
                    $totalSize += $this->size($filepath);
                }

                return $totalSize;
            }
            else {
                return filesize($this->toInternalPath($path));
            }
        }

        /**
         * Get the file's size in octects
         *
         * @param string $path The path to the file
         *
         * @return string
         */
        public function sizeInOctets($path) {
            $size = $this->size($path);
            $unit = 'b';

            if ($size > 1024) {
                $size = $size / 1024;
                $unit = 'Kb';
            }
            if ($size > 1024) {
                $size = $size / 1024;
                $unit = 'Mb';
            }
            if ($size > 1024) {
                $size = $size / 1024;
                $unit = 'Gb';
            }
            if ($size > 1024) {
                $size = $size / 1024;
                $unit = 'Tb';
            }

            return round($size, 2) . ' ' . $unit;
        }

        /**
         * Get the file's MIME type.
         *
         * @param  string $path The file's path.
         *
         * @return string
         */
        public function mimetype($path) {
            $fileExtension = $this->extension($path);

            switch ($fileExtension) {
                case 'css':
                    return 'text/css';
                case 'js':
                    return 'text/javascript';
                case 'html':
                    return 'text/html';
            }

            $htaccessPath = '/.htaccess';
            if ($this->exists($htaccessPath)) {
                $contents = $this->read($htaccessPath);
                $lines = explode("\n", $contents);

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (stripos($line, 'AddType ') === 0) {
                        $data = explode(' ', $line);
                        if ($data[2] == $fileExtension) {
                            return $data[1];
                        }
                    }
                }
            }

            if (!class_exists('finfo')) {
                return 'application/octet-stream';
            }
            $finfo = new \finfo(FILEINFO_MIME);
            if (!$finfo) {
                return 'application/octet-stream';
            }
            return $finfo->file($this->toInternalPath($path));
        }

        /**
         * Check if a file is a binary file.
         *
         * @param  string  $path The file path.
         *
         * @return boolean
         */
        public function isBinary($path) {
            $mime = $this->mimetype($path);
            return (substr($mime, 0, 5) != 'text/');
        }

        /**
         * Get an information about a specified path
         *
         * @param string $path The path to get the information
         * @param int    $info The information to get about the path
         *
         * @access protected
         *
         * @return string
         */
        protected function pathInfo($path, $info) {
            $internalPath = $this->cleanPath($path);

            switch ($info) {

                case PATHINFO_BASENAME :
                    return basename($internalPath);

                case PATHINFO_EXTENSION :
                    $basename = basename($internalPath);
                    if (strrpos($basename, '.') !== FALSE) {
                        return substr($basename, strrpos($basename, '.')+1);
                    }
                    else {
                        if ($this->isDir($internalPath)) {
                            return 'folder';
                        }
                        else {
                            return 'file';
                        }
                    }
                break;

                case PATHINFO_FILENAME :
                    $basename = basename($internalPath);
                    return substr($basename, 0, strrpos($basename, '.'));

                case PATHINFO_DIRNAME:
                    if (strpos($path, '/', 1) !== FALSE) {
                        $dirname = preg_replace('#/[^/]*/?$#', '', $path);
                    }
                    elseif (strpos($path, '/') === 0) {
                        $dirname = '/';
                    }
                    else {
                        $dirname = FALSE;
                    }

                    if ($dirname == '.') {
                        $dirname = FALSE;
                    }

                    if ($dirname == $this->toFileSystemPath('/users') || $dirname == $this->toInternalPath('/users')) {
                        $dirname = FALSE;
                    }

                    return $dirname;

            }
        }

        /**
         * Read all elements in a directory
         *
         * @param string    $path       The path of the diretory
         * @param boolean   $recursive  Define if the directory have to be readed recursively
         * @param array     $options    Additionnal options to use :
         *                                  path_type => The type of file path;
         *                                  file_type => The extension(s) of files to select;
         *                                  filter    => The extension(s) of files to ignore.
         *
         * @return array
         */
        public function readDir($path, $recursive = FALSE, $options = array('path_type' => self::REAL_PATH, 'file_type' => false, 'filter' => false)) {
            if (!$this->isRemote($path) && !is_readable($this->toInternalPath($path))) {
                throw $this->accessDeniedException($path, 'read');
            }

            $options['path_type'] = !isset($options['path_type']) ? self::REAL_PATH : $options['path_type'];
            $options['file_type'] = !isset($options['file_type']) ? FALSE           : $options['file_type'];
            $options['filter']    = !isset($options['filter'])    ? FALSE           : $options['filter'];

            $path   = $this->toInternalPath($path);
            $files  = array();

            if ($handle = opendir($path)) {
                while (($file = readdir($handle)) !== FALSE) {
                    $filepath = $this->cleanPath(make_path(array($path, $file)));

                    // Removing dirty
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    // Applying filters
                    if (is_string($options['filter']) && $this->extension($filepath) == $options['filter']) {
                        continue;
                    }
                    if (is_array($options['filter']) && in_array($this->extension($filepath), $options['filter'])) {
                        continue;
                    }
                    // Skipping unwanted files
                    if (is_string($options['file_type']) && $this->extension($filepath) != $options['file_type']) {
                        continue;
                    }
                    if (is_array($options['file_type']) && !in_array($this->extension($filepath), $options['file_type'])) {
                        continue;
                    }

                    switch ($options['path_type']) {
                        default:
                        case self::REAL_PATH:
                            $files[$file] = $filepath;
                        break;

                        case self::INTERNAL_PATH:
                            $files[$file] = $this->toInternalPath($filepath);
                        break;

                        case self::EXTERNAL_PATH:
                            $files[$file] = $this->toExternalPath($filepath);
                        break;

                        case self::FILESYSTEM_PATH:
                            $files[$file] = $this->toFileSystemPath($filepath);
                        break;
                    }

                    if ($recursive === TRUE && $this->isDir($filepath)) {
                        $subfiles = $this->readDir($filepath, $recursive, $options);
                        foreach($subfiles as $subfilename => $subfilepath) {
                            $files[make_path(array($file, $subfilename))] = $subfilepath;
                        }
                    }
                }
                closedir($handle);

                ksort($files);

                return $files;
            }
            else {
                throw $this->accessDeniedException($path, 'open');
            }

        }

        /**
         * Check if the path is remote
         *
         * Returns TRUE if the path is remote,
         * and FALSE otherwise.
         *
         * @param string $path The path to Check
         *
         * @return boolean
         */
        public function isRemote($path) {
            return (strpos($path, '://') !== FALSE);
        }

        /**
         * Remove the hostname from the path
         *
         * @param string $path The path to revome the hostname
         *
         * @return string
         */
        public function removeHostFromPath($path) {
            return parse_url($path, PHP_URL_PATH);
        }

        /**
         * Clean the path for bad directory name
         *
         * @param string $path The path to clean
         *
         * @return string
         */
        public function cleanPath($path) {
            if ($this->isRemote($path)) {
                return $path;
            }

            $badDirs   = explode(DIRECTORY_SEPARATOR, $path);
            $cleanDirs = array();
            foreach ($badDirs as $i => $dir) {
                if ($dir == '..') {
                    array_pop($cleanDirs);
                } elseif ($dir == '.') {
                    continue;
                } elseif (empty($dir) && $i > 0) {
                    continue;
                } else {
                    $cleanDirs[] = $dir;
                }
            }

            if ($path != '/') {
                $beautifiedPath = implode(DIRECTORY_SEPARATOR, $cleanDirs);
            }

            if (empty($beautifiedPath)) {
                $beautifiedPath = (substr($path, 0, 1) == '/') ? '/' : '.';
            }

            return $beautifiedPath;
        }

        /**
         * Transform a path to an internal FileSystem path
         *
         * @param string $path The path to transform
         *
         * @return string
         */
        public function toInternalPath($path) {
            $internalPath = $path;

            // Do nothing if is a remote path
            if ($this->isRemote($internalPath)) {
                return $internalPath;
            }

            if (substr($internalPath, 0, strlen($this->rootpath())) == $this->rootpath()) {
                $internalPath = str_replace($this->rootpath(), './', $internalPath);
            } else {
                $realRootPath = realpath($this->rootpath());
                if (substr($internalPath, 0, strlen($realRootPath)) == $realRootPath) {
                    $internalPath = substr($internalPath, strlen($realRootPath));
                }
            }

            // Convert relative path to absolute path
            if (preg_match('#^(\.)+/#', $internalPath)) {
                $internalPath = make_path(array($this->workingDir(), $internalPath));
            }

            // Apply aliases
            $nbrTurns    = 0;
            $maxNbrTurns = count($this->aliases);
            do {
                $appliedAliasesNbr = 0;

                foreach ($this->aliases as $key => $value) {
                    if (substr($internalPath, 0, strlen($key)) == $key) {
                        $internalPath = make_path(array($value, substr($internalPath, strlen($key))));
                        $appliedAliasesNbr++;
                    }
                }

                $nbrTurns++;
            } while($appliedAliasesNbr > 0 && $nbrTurns <= $maxNbrTurns);

            // Prepend the root path
            $rootPath = $this->rootpath();
            if (!empty($rootPath)) {
                $internalPath = make_path(array($rootPath, $internalPath));
            }

            return $this->cleanPath($internalPath);
        }

        /**
         * Convert an internal path to an external path.
         *
         * @param  string $internalPath The internal path.
         *
         * @return string
         */
        public function toExternalPath($internalPath) {
            $externalPath = $internalPath;

            if ($this->isRemote($externalPath)) {
                return $externalPath;
            }

            if (substr($externalPath, 0, strlen($this->rootpath())) == $this->rootpath()) {
                $externalPath = substr($externalPath, strlen($this->rootpath()));
            } else {
                $realRootPath = realpath($this->rootpath());
                if (substr($externalPath, 0, strlen($realRootPath)) == $realRootPath) {
                    $externalPath = substr($externalPath, strlen($realRootPath));
                }
            }

            if ($externalPath[0] != '/') {
                return $internalPath;
            }

            $nbrTurns = 0;
            $maxNbrTurns = count($this->aliases);
            do {
                $appliedAliasesNbr = 0;

                foreach ($this->aliases as $key => $value) {
                    $value = '/'.$value;
                    if (substr($externalPath, 0, strlen($value)) == $value) {
                        $externalPath = make_path(array($key, substr($externalPath, strlen($value))));
                        $appliedAliasesNbr++;
                    }
                }

                $nbrTurns++;
            } while($appliedAliasesNbr > 0 && $nbrTurns <= $maxNbrTurns);

            return $this->cleanPath($externalPath);
        }


        /**
         * Convert an internal path to an absolute path
         * which start on the filesystem rootpath
         *
         * @param  string $internalPath The internal path.
         *
         * @return string
         */
        public function toFileSystemPath($internalPath) {
            $externalPath = $internalPath;

            if ($this->isRemote($externalPath)) {
                return $externalPath;
            }

            if (substr($externalPath, 0, strlen($this->rootpath())) == $this->rootpath()) {
                $externalPath = substr($externalPath, strlen($this->rootpath()));
            } else {
                $realRootPath = realpath($this->rootpath());
                if (substr($externalPath, 0, strlen($realRootPath)) == $realRootPath) {
                    $externalPath = substr($externalPath, strlen($realRootPath));
                }
            }

            // Apply aliases
            $nbrTurns    = 0;
            $maxNbrTurns = count($this->aliases);
            do {
                $appliedAliasesNbr = 0;

                foreach ($this->aliases as $key => $value) {
                    if (substr($externalPath, 0, strlen($key)) == $key) {
                        $externalPath = make_path(array($value, substr($externalPath, strlen($key))));
                        $appliedAliasesNbr++;
                    }
                }

                $nbrTurns++;
            } while($appliedAliasesNbr > 0 && $nbrTurns <= $maxNbrTurns);

            while (substr($externalPath, 0, 1) == '/' || substr($externalPath, 0, 2) == './') {
                $externalPath = substr($externalPath, 1);
            }

            return $this->cleanPath($externalPath);
        }

        /**
         * Used to throw an access denied exception
         *
         * @param string $path   The path to the file
         * @param string $action The type of access denied
         *
         * @return RuntimeException
         */
        protected function accessDeniedException($path, $action = 'write') {
            $msg = "Cannot {$action} the file \"{$path}\": permission denied";
            if (!$this->isRemote($path)) {
                $msg .= " (The web server user cannot {$action} files, chmod needed)";
            }
            return new \RuntimeException($msg);
        }

    }
