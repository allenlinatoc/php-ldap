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
use Nette\Caching\Cache as NetteCache;
use Nette\Caching\Storages\FileStorage;

/**
 * Description of Cache
 *
 * @author alinatoc
 */
class Cache extends NetteCache
{


    /**
     * New instance of Cache
     *
     * @param string $namespace     [optional] Cache namespace
     */
    public function __construct($namespace = NULL)
    {
        $storage = new FileStorage(sys_get_temp_dir());
        parent::__construct($storage, $namespace);
    }


    /**
     * Check if a key exists in this cache
     *
     * @param string $key       The data key to be checked
     * @return boolean
     */
    public function has($key)
    {
        return $this->load($key) !== null;
    }


    /**
     * Save a data with expiration. Returns saved data value
     *
     * @param string $key               Data key
     * @param mixed $data               Data to be saved
     * @param string|int $expiration    [optional] If expiration in XX minutes or timestamp
     * @return mixed
     */
    public function saveWithExpiration($key, $data, $expiration = '5 minutes')
    {
        return $this->save($key, $data, [ Cache::EXPIRATION => $expiration ]);
    }


    /**
     * Create new instance of Cache
     *
     * @param string $namespace     [optional] Cache namespace
     * @return \allenlinatoc\phpldap\Cache
     */
    static public function Create($namespace = null)
    {
        return new Cache($namespace);
    }


}
