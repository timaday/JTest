<?php

namespace TAS\Definition\Testers;

class TestProfileManager
{
    protected $tester;
    protected $testerName;
    protected $credentials;
    /** @var object Behat\Mink\Session */
    Protected $session;

    protected $module;

    public function __construct(array $credentials, $module)
    {
        $this->module = $module;
        $this->credentials = $credentials;
    }

    public function getTester($name)
    {
        if (isset($this->tester) &&
            ($this->getTesterName() === $name || $name === null )
        ) {
          return $this->tester;
        }
        if ($name === 'UnauthenticatedTester') {
            $this->tester = new Tester(
                $name,
                ['PASSWORD' => '', 'USERNAME' => ''],
                $this->module
            );
            $this->testerName = $name;
        } else {
            $this->tester = new Tester(
                $name,
                $this->parseCredentials($name),
                $this->module
            );
            $this->testerName = $name;
        }
        if (empty($this->tester)) {
            throw new \RuntimeException('Tester was not defined during test run.');
        }

        return $this->tester;
    }

    private function parseName($name)
    {
       return str_replace(' ', '', ucwords($name));
    }

    public function getTesterName()
    {
        return $this->testerName ?: null;
    }

    private function parseCredentials($name){
        $parseName = $this->parseName($name);
        if (!isset($this->credentials[$parseName]['PASSWORD']) || !isset($this->credentials[$parseName]['USERNAME'])) {
           throw new \InvalidArgumentException('Test credentials do not exist for ' . $name);
        }
        return $this->credentials[$parseName];
    }
}