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

/**
 * Description of PhpLdapStub
 *
 * @author alinatoc
 */
class PhpLdapStub
{

    const STDIO = '{stdio}';
    const ENCRYPTION_SSL = 'SSL';
    const ENCRYPTION_TLS = 'TLS';


    public
            // Required fields

            $server,
            $port,
            $username,
            $password,
            $accountDomainName,
            $baseDn,

            // Optionals

            $ymlfile,
            $filter,
            $attributes,
            $keyAttribute,
            $isSSL,
            $output
    ;



    public function __construct($config = [ ])
    {
        if (empty($config))
        {
            // Get arguments

            $argManager = new ArgumentManager();
            $argManager->parse();

        }

        $config = $argManager->asArray();

        $this->server = $config['server'];
        $this->port = $config['port'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->accountDomainName = $config['domain'];
        $this->baseDn = $config['basedn'];

        // optionals
        $this->filter = isset($config['filter']) && $config['filter'] ? $config['filter'] : '(objectClass=*)';
        $this->attributes = isset($config['attributes']) && $config['attributes'] ? $config['attributes'] : [ ];
        $this->keyAttribute = strtolower(isset($config['keyattr']) && $config['keyattr'] ? $config['keyattr'] : 'sAMAccountName');
        $this->isSSL = isset($config['ssl']) && $config['ssl'] ? true : null;
        $this->output = isset($config['output']) && $config['output'] ? $config['output'] : self::STDIO;

        // check if output needs to be verified
        if ($this->output != self::STDIO)
        {
            // Get base directory
            $dir = dirname($this->output);
            if (!file_exists($dir) && !is_dir($this->output))
            {
                mkdir($this->output, 0777, true);
            }
            if (file_exists($this->output) && is_file($this->output))
            {
                unlink($this->output);
            }
        }


        // Create LDAP connection
        $ldap = new \Zend\Ldap\Ldap([
            'accountDomainName' => $this->accountDomainName,
            'baseDn' => $this->baseDn
        ]);
        $ldap->connect($this->server, $this->port, $this->isSSL);
        $ldap->bind($this->username . '@' . $this->accountDomainName, $this->password);


        // Apply filter
        $result = $ldap->search($this->filter);

        // Prepare output container
        $output = [ ];


        foreach ($result as $index => $entry)
        {
            $newrow = [ ];
            foreach ($entry as $key => $value)
            {
                if ($this->attributeIncluded($key))
                {
                    if (is_array($value) && sizeof($value) == 1)
                    {
                        $value = $value[0];
                    }

                    $newrow[$key] = $value;
                }
            }

            // Push attribute
            if (isset($newrow[$this->keyAttribute]))
            {
                $output[$newrow[$this->keyAttribute]] = $newrow;
            }
            else
            {
                $output[] = $newrow;
            }
        }

        $ymlOutput = \Spyc::YAMLDump($output, true);
        if ($this->output == self::STDIO)
        {
            echo $ymlOutput;
        }
        else
        {
            $writeSuccess = file_put_contents($this->output, $ymlOutput);
            echo (
                    $writeSuccess !== false ?
                        PHP_EOL . sprintf("Output has been successfully exported to \"%s\"", realpath($this->output))
                      : sprintf("Unable to write output to file \"%s\"")
                ) . PHP_EOL . PHP_EOL
            ;
        }


    }


    /**
     * Check if this attribute is included
     *
     * @param type $attribute
     * @return type
     */
    public function attributeIncluded($attribute)
    {
        return empty($this->attributes) ? true : $this->attributes[$attribute];
    }


    public function getValue($result, $index, $attribute)
    {
        $result = $result instanceof \Zend\Ldap\Collection ? $result->toArray() : $result;
        $value = $result[$index];

//        var_dump($value);
//        die();
        $attrValue = $value[$attribute];

        return is_array($attrValue) ?
                (sizeof($attrValue) > 1 ? implode(', ', $attrValue) : $attrValue[0])
              : $attrValue;
    }



}
