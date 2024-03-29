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
     *@param string $path path to .validcolumns file

     * @return array
     */
    public static function getValidColumns($path) : array
    {
        $filepath = $_SERVER['DOCUMENT_ROOT'] . $path . '/.validcolumns';

        if (!file_exists($filepath)) {
            throw new \Exception('Failed to find .validcolumns file, cannot invoke Database');
        }
        $validColumnsFile = file_get_contents($filepath);
        if (empty($validColumnsFile)) {
            return [];
        }
        // explode with end of line as separator
        return explode(PHP_EOL, $validColumnsFile);
    }

    /**
     * getValidTables
     *
     * Gets list of valid tables from .validtables file
     * @param string $path path to .validtables file
     *
     * @return array
     */
    public static function getValidTables($path) : array
    {
        $filepath = $_SERVER['DOCUMENT_ROOT'] . $path . '/.validtables';
        if (!file_exists($filepath)) {
            throw new \Exception('Failed to find .validtables file, cannot invoke Database');
        }
        $validTablesFile = file_get_contents($filepath);
        if (empty($validTablesFile)) {
            return [];
        }
        // explode with end of line as separator
        return explode(PHP_EOL, $validTablesFile);
    }
}
