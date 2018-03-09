<?php
class Sys_Database
{
    public static function getTable($name) {
        $returns = null;

        $common = Yaf_Registry::get('common_config');

        if (isset($common['table'][$name])) {
            $returns = $common['table'][$name];
        }

        return $returns;
    }
} 