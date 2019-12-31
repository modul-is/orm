<?php

namespace Core;

use Nette;

class MySqlDriver extends \Nette\Database\Drivers\MySqlDriver
{
    /**
     * Returns associative array of detected types (IReflection::FIELD_*) in result set.
     */
    public function getColumnTypes(\PDOStatement $statement)
    {
        $types = [];
        $count = $statement->columnCount();
        for($col = 0; $col < $count; $col++)
        {
            $meta = $statement->getColumnMeta($col);
            if(isset($meta['native_type']))
            {
                $types[$meta['name']] = $type = Nette\Database\Helpers::detectType($meta['native_type']);

                if($type === Nette\Database\IStructure::FIELD_TIME)
                {
                    $types[$meta['name']] = Nette\Database\IStructure::FIELD_TIME_INTERVAL;
                }
                elseif($type == Nette\Database\IStructure::FIELD_DATE || $type == Nette\Database\IStructure::FIELD_DATETIME)
                {
                    $types[$meta['name']] = Nette\Database\IStructure::FIELD_TEXT;
                }
            }
        }

        return $types;
    }
}
