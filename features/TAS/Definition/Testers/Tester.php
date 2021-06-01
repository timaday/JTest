<?php

namespace TAS\Definition\Testers;

class Tester extends TestProfileManager
{
    /** @var string username */
    protected $username;

    /** @var string Password */
    protected $password;

    /** @var string Tester Name - #Test reference */
    Protected $testerName;

    public function __construct($testerName, $configuration, $module = null) {
        if (!isset($configuration['PASSWORD']) || !isset($configuration['USERNAME'])) {
            throw new \InvalidArgumentException('Login configuration did not load');
        }
        $this->testerName = $testerName;
        $this->password = $configuration['PASSWORD'];
        $this->username = $configuration['USERNAME'];
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getModule()
    {
        return $this->module;
    }
}