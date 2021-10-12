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
    
    /**
     * Realiza alguns tratamentos na string @param $str, tais como
     * remoção de espaços em branco do início e do final da variável,
     * codificar os caracteres especiais do HTML e
     * adicionar slashes antes de aspas e barras.
     */
    static function sanitizeString(?string $str): ?string {
        if ($str) {
            $str = trim($str);
            $str = htmlspecialchars($str);
            return addslashes($str);
        }
        return null;
    }

    /**
     * Remove os tratamentos realizados pela função sanitizeString().
     */
    static function stripString(?string $str): ?string {
        if ($str) {
            $str = htmlspecialchars_decode($str);
            return stripslashes($str);
        }
        return null;
    }
}