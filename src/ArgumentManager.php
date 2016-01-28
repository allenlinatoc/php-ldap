<?php

/*
 * The MIT License
 *
 * Copyright 2016 alinatoc.
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
 */

namespace allenlinatoc\phpldap;

use cli\Arguments;
use allenlinatoc\phpldap\exceptions\RequiredArgumentException;

/**
 * Description of ArgumentManager
 *
 * @author alinatoc
 */
class ArgumentManager extends Arguments
{

    static private $required = [
        'server',
        'port',
        'domain',
        'basedn',
        'username',
        'password'
    ];


    /**
     * New instance of ArgumentManager
     */
    public function __construct()
    {
        parent::__construct([ 'strict' => true ]);
        $this->initOptions();
    }


    /**
     * Initialize argument manager options
     */
    private function initOptions()
    {
        $this->addOption([ 'server', 'S' ], 'LDAP server domain or hostname');
        $this->addOption([ 'port', 'P' ], 'LDAP server port');
        $this->addOption([ 'domain', 'D' ], 'Account domain name');
        $this->addOption([ 'basedn', 'B' ], 'Base DN containing AD users');

        $this->addOption([ 'username', 'U' ], 'Username of account during bind');
        $this->addOption([ 'password', 'P' ],
                [
                    'description' => 'Password of account during bind',
                    'default' => ''
                ]);

        $this->addOption([ 'ymlfile', 'y' ],
                [
                    'description' => '(optional) Arguments YAML file',
                    'default' => '{null}'
                ]);

        $this->addOption([ 'filter', 'f' ],
                [
                    'description' => '(optional) Query filter',
                    'default' => '(objectClass=*)'
                ]);

        $this->addOption([ 'attributes' ], '(optional) Comma-separated AD attributes to be fetched');

        $this->addOption([ 'output' ],
                [
                    'description' => '(optional) Filename where output will be written',
                    'default' => PhpLdapStub::STDIO
                ]);

        $this->addOption([ 'keyattr' ],
                [
                    'description' => '(optional) Key attribute per result entry',
                    'default' => 'sAMAccountName'
                ]);

        $this->addFlag([ 'ssl', 's' ], 'If connection will use SSL');
        $this->addFlag([ 'help', 'h' ], 'Show this help');
    }


    /**
     * Parse input arguments
     */
    public function parse()
    {
        try
        {
            parent::parse();

            if ($this['help'])
            {
                echo PHP_EOL;
                die($this->getHelpScreen() . PHP_EOL . PHP_EOL);
            }

            // Check if there's YML argument file
            $ymlconfig = $this->processYMLfile();
            if ($ymlconfig !== false)
            {
                foreach ($ymlconfig as $key => $value)
                {
                    if (!isset($this[$key]))
                    {
                        $this->offsetSet($key, $value);
                    }
                }
            }

            // Check undefined required arguments
            $undefined = [ ];
            foreach (self::$required as $required_field)
            {
                if (!isset($this[$required_field]))
                    $undefined[] = $required_field;
            }

            // If there are undefined required arguments
            if (sizeof($undefined) > 0)
            {
                throw new RequiredArgumentException($undefined);
            }

        }
        catch (\cli\arguments\InvalidArguments $ex)
        {
            $arguments = $ex->getArguments();
            array_walk($arguments, function(&$value)
                    {

                        $value = "    " . $value . PHP_EOL;

                    });

            echo "Invalid arguments supplied: " . PHP_EOL . PHP_EOL;
            foreach ($arguments as $arg)
            {
                echo $arg;
            }
            echo PHP_EOL;
            die($this->getHelpScreen() . PHP_EOL . PHP_EOL);
        }
        catch (RequiredArgumentException $ex)
        {
            echo $ex->getMessage() . ': ' . implode(', ', $ex->getArguments()) . PHP_EOL;
            die($this->getHelpScreen() . PHP_EOL . PHP_EOL);
        }
        catch (\Exception $ex)
        {
            die($arguments = $ex->getMessage() . PHP_EOL . PHP_EOL);
        }


    }


    /**
     * Process YML file, if any specified, with the arguments group
     *
     * @return array|FALSE
     */
    public function processYMLfile()
    {

        // Check if args file is defined
        if (isset($this['ymlfile']) && $this['ymlfile'] != '{null}')
        {
            $cache = Cache::Create(__CLASS__);
            $ymlfile = $this->offsetGet('ymlfile');

            // Check if args file exists
            if (!file_exists($ymlfile))
            {
                die(sprintf('Arguments file "%s" does not exist' . PHP_EOL . PHP_EOL, $ymlfile));
            }

            // Otherwise load it
            $this->offsetSet('ymlfile', realpath($this->offsetGet('ymlfile')));
            return \Spyc::YAMLLoad($this->offsetGet('ymlfile'));
        }

        return false;
    }


    /**
     * Get the arguments parsing result as array
     *
     * @return array
     */
    public function asArray()
    {
        return json_decode($this->asJSON(), true);
    }


}
