<?php

namespace System;

class Utils {

    /**
     * Retorna o elemento na posição (@param $key) do array, caso não
     * exista, (@param $default) é retornado.
     */
    static function getFromArray($key, $array, $default = null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}