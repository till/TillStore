<?php
/**
 * TillStore.php
 *
 * PHP Version 5
 *
 * @category Database
 * @package  TillStore
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.html BSD License
 * @version  SVN: $Id$
 * @link     http://github.com/till/TillStore
 */

/**
 * TillStore_Exception
 */
require_once 'TillStore/Exception.php';

/**
 * Inspired by the nosql berlin meetup, here's a key value store in PHP! :-)
 *
 * @category Database
 * @package  TillStore
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.html BSD License
 * @version  Release: @package_version@
 * @link     http://github.com/till/TillStore
 */
class TillStore
{
    protected $device = '/dev/shm';
    protected $key    = 'TillStore';

    /**
     * Construct
     *
     * @param mixed $device The device to write to, standard: /dev/shm
     *
     * @return $this
     */
    public function __construct($device = null)
    {
        if ($device !== null) {
            $this->device = $device;
        }
    }

    /**
     * Delete a key.
     *
     * @param string $var The name of the key.
     *
     * @return boolean 'true' if the operation is successful, 'false' otherwise.
     * @uses   self::getFilename()
     */
    public function delete($var)
    {
        $var = trim($var);
        if (empty($var)) {
            return false;
        }
        $filename = $this->getFilename($var);
        if (!file_exists($filename)) {
            return false;
        }
        if (!is_readable($filename)) {
            return false;
        }
        return @unlink ($filename);
    }

    /**
     * Set a value!
     *
     * @param string $var   The name of the key.
     * @param mixed  $value Any data type (int, float, string, object, array, ...)
     * @param mixed  $ttl   Optional: The time to live, in seconds.
     *
     * @return mixed
     */
    public function set($var, $value, $ttl = null)
    {
        if ($ttl !== null) {
            if (!is_int($ttl)) {
                throw new TillStoreException("Time to live has to be an integer.");
            }
        }
        $filename = $this->getFilename($var);
        $status   = $this->write($filename, $value, $ttl);
        return $value;
    }

    /**
     * Garbage collection. Clean up all expired items.
     *
     * @return void
     */
    public function gc()
    {
        foreach (glob("{$this->device}/{$this->key}-*") as $value) {
            $data = file_get_contents($value);
            $data = json_decode($data);
            if (empty($data->ttl)) {
                continue;
            }
            if ($data->ttl >= mktime()) {
                continue;
            }
            unlink($value);
        }
        return;
    }

    /**
     * Get an item from TillStore!
     *
     * @param string $var     The name of the key.
     * @param mixed  $default The default value to return when nothing is found.
     *
     * @return mixed
     * @uses   self::read()
     * @throws TillStoreException In case the key exists, but is not readable.
     */
    public function get($var, $default = null)
    {
        $data = $this->read($var, $default);
        if (!is_object($data) || empty($data)) {
            return $default;
        }
        return unserialize($data->value);
    }

    /**
     * Return an item's time to live!
     * 
     * @param string $var The name of the key.
     *
     * @return mixed
     * @uses   self::read()
     */
    public function getTtl($var)
    {
        $data = $this->read($var);
        return $data->ttl;
    }

    /**
     * Create a filename from a variable.
     *
     * @param string $var The name of the key.
     *
     * @return string
     */
    protected function getFilename($var)
    {
        return $this->device . '/' . $this->key . '-' . md5(trim($var));
    }

    /**
     * Read the key file.
     *
     * @param string $var     The name of the key.
     * @param mixed  $default Optional: the default value.
     *
     * @return object
     * @throws TillStoreException On permission error - when we can't read the file.
     *
     * @see  self::get()
     * @see  self::getTtl()
     * @uses self::getFileName()
     */
    protected function read($var, $default = null)
    {
        $filename = $this->getFileName($var);
        if (!file_exists($filename)) {
            return $default;
        }
        if (!is_readable($filename)) {
            throw new TillStoreException("Internal read error.");
        }
        $data = file_get_contents($filename);
        $data = json_decode($data);
        return $data;
    }

    /**
     * Write to the key value store!
     *
     * This is pretty simple, we serialize the object -- just to be sure, create an
     * array and push it in, along with the TTL.
     *
     * @param string $filename The filename to write the key to.
     * @param mixed  $value    The value of the key.
     * @param mixed  $ttl      The time to live.
     *
     * @return true
     * @throws TillStoreException When the write fails.
     * @see    self::set()
     */
    protected function write($filename, $value, $ttl)
    {
        $fp = fopen($filename, 'w+');
        if ($fp === false) {
            throw new TillStoreException("Unable to write data.");
        }

        $expire = null;
        if ($ttl !== null) {
            $expire = mktime() + $ttl;
        }

        $data = array(
            'value' => serialize($value),
            'ttl'   => $expire,
        );
        $data = json_encode($data);

        fwrite($fp, $data);
        fclose($fp);
        return true;
    }
}