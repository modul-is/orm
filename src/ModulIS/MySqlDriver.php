<?php

declare(strict_types = 1);

namespace ModulIS;

use Nette\Database\Drivers\MySqlDriver as NetteMySqlDriver;
use Nette\Database\Helpers;
use Nette\Database\IStructure;
use PDOStatement;


class MySqlDriver extends NetteMySqlDriver
{
	/**
	 * @note Returns associative array of detected types (IReflection::FIELD_*) in result set.
	 */
	public function getColumnTypes(PDOStatement $statement): array
	{
		$types = [];
		$count = $statement->columnCount();

		for($col = 0; $col < $count; $col++)
		{
			$meta = $statement->getColumnMeta($col);
			if(isset($meta['native_type']))
			{
				$types[$meta['name']] = $type = Helpers::detectType($meta['native_type']);

				if($type === IStructure::FIELD_TIME)
				{
					$types[$meta['name']] = IStructure::FIELD_TIME_INTERVAL;
				}
				elseif($type == IStructure::FIELD_DATE || $type == IStructure::FIELD_DATETIME)
				{
					$types[$meta['name']] = IStructure::FIELD_TEXT;
				}
			}
		}

		return $types;
	}
}
