<?php

namespace Database;

class DBAllowList
{
    public function __construct()
    {
    }

    /**
     * getValidColumns
     *
     * Gets list of valid columns from .validcolumns file
     *
     * @return array
     */
    public static function getValidColumns() : array
    {
        $filepath = __DIR__ . '/.validcolumns';
        if (!file_exists($filepath)) {
            $filepath = __DIR__ . '/.validcolumns.sample';
            if (!file_exists($filepath)) {
                throw new \Exception('Failed to find .validcolumns or .validcolumns.sample file, cannot invoke Database');
            }
        }
        $validColumnsFile = file_get_contents($filepath);
        if(empty($validColumnsFile)){
            return [];
        }
        // explode with end of line as separator
        return explode(PHP_EOL, $validColumnsFile);
    }

    /**
     * getValidTables
     *
     * Gets list of valid tables from .validtables file
     *
     * @return array
     */
    public static function getValidTables() : array
    {
        $filepath = __DIR__ . '/.validtables';
        if (!file_exists($filepath)) {
            $filepath = __DIR__ . '/.validtables.sample';
            if (!file_exists($filepath)) {
                throw new \Exception('Failed to find .validtables.sample or .validtables.sample file, cannot invoke Database');
            }
        }
        $validTablesFile = file_get_contents($filepath);
        if(empty($validTablesFile)){
            return [];
        }
        // explode with end of line as separator
        return explode(PHP_EOL, $validTablesFile);
    }
}
