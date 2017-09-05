<?php

namespace aryelgois\cnab240\example;

use aryelgois\utils;

class Database extends utils\Database
{
    /**
     * Simply output the error message like a FATAL
     */
    public function error($message, $opt = null)
    {
        die($message);
    }
    
    /**
     * Returns the first row of a successful mysqli query
     *
     * @param object $mysqli A \mysqli_result object for query()
     *                       Or a \mysqli_stmt object for prepare()
     *
     * @return null or mixed[]
     */
    public static function getFirst($mysqli)
    {
        $result = self::fetch($mysqli);
        if (empty($result)) {
            return null;
        }
        return $result[0];
    }
}
