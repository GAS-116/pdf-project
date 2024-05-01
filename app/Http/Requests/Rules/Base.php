<?php

namespace App\Http\Requests\Rules;

use Illuminate\Support\Facades\Validator;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

abstract class Base
{
    protected $data;

    abstract public function fields();

    public function getRules()
    {
        $recursiveIterator = new RecursiveArrayIterator($this->fields());
        $recursiveIterIter = new RecursiveIteratorIterator($recursiveIterator);

        $rules = [];
        foreach ($recursiveIterIter as $key => $value) {
            $d = $recursiveIterIter->getDepth();
            $path = $key;

            if ($d != 0) {
                for ($i = $recursiveIterIter->getDepth() - 1; $i >= 0; $i--) {
                    $path = $recursiveIterIter->getSubIterator($i)->key().'.'.$path;
                }
            }
            $rules[$path] = $value;
        }

        return $rules;
    }
}
