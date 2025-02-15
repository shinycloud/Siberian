<?php

/**
 * Class Application_Model_Device_Abstract
 */
abstract class Application_Model_Device_Abstract extends Core_Model_Default
{
    /**
     * @var string
     */
    public $_os_name = "__unset__";

    /**
     * @var
     */
    public $_logger;

    /**
     * @return string
     */
    public function getOsName()
    {
        return $this->_os_name;
    }

    /**
     * @return mixed
     */
    public function getResources()
    {
        $umask = umask(0);

        $resource = $this->prepareResources();
        umask($umask);

        return $resource;
    }

    /**
     * @param $replacements
     * @param $file
     * @param bool $regex
     * @throws Siberian_Exception
     */
    protected function __replace($replacements, $file, $regex = false)
    {
        $contents = file_get_contents($file);
        if (!$contents) {
            throw new Siberian_Exception(__('An error occurred while editing file (%s).', $file));
        }

        foreach ($replacements as $search => $replace) {
            if ($regex) {
                $contents = preg_replace($search, $replace, $contents);
            } else {
                $contents = str_replace($search, $replace, $contents);
            }

        }
        file_put_contents($file, $contents);
    }

    /**
     * Archive the generated project.
     *
     * @return string
     * @throws Exception
     */
    protected function zipFolder()
    {

        $folder = $this->_dest_source;
        if (!isset($this->_dest_archive)) {
            $dest = "{$this->_dest_source}/{$this->_zipname}.zip";
        } else {
            $dest = "{$this->_dest_archive}/{$this->_zipname}.zip";
        }

        Core_Model_Directory::zip($folder, $dest);

        if (!file_exists($dest)) {
            throw new Exception("An error occurred during the creation of the archive ({$dest})");
        }

        return $dest;
    }
}
