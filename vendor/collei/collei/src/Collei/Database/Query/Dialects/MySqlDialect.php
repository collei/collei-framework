<?php
namespace Collei\Database\Query\Dialects;

use Collei\Utils\Arr;

/**
 *	Specifies the basic query structs for MySQL
 *
 *	@author alarido <alarido.su@gmail.com>
 *	@since 2021-12-06
 */
class MySqlDialect extends Dialect
{
	/**
	 *	Outlines the general, simpler create table structure
	 *
	 *	the $fields parameter is an array of numeric or associative arrays as follows
	 *			$fields[n][0] or $fields[n]['name']			name of the field
	 *			$fields[n][1] or $fields[n]['type']			data type of the field
	 *			$fields[n][2] or $fields[n]['length']		maximum length of the field
	 *			$fields[n][3] or $fields[n]['precision']	precision (if any) of the field
	 *			$fields[n][4] or $fields[n]['nullable']		tells if field is nullable or not
	 *	the $primaryKey parameter is a numeric or associative array as follows
	 *			$primaryKey[0] or $primaryKey['name']				name of the PK
	 *			$primaryKey[1] or $primaryKey['type']				data type of the PK
	 *			$primaryKey[2] or $primaryKey['length']				maximum length of the PK (if any)
	 *			$primaryKey[3] or $primaryKey['precision']			precision of the PK (if any)
	 *			$primaryKey[4] or $primaryKey['auto_increment']		tells if field is auto numbered
	 *	the $foreignKeys parameter is an array of numeric or associative arrays as follows
	 *			$fields[n][0] or $fields[n]['name']			name of the constraint
	 *			$fields[n][1] or $fields[n]['key']				name of the key (one of table columns)
	 *			$fields[n][2] or $fields[n]['foreign_table']	name of referenced table
	 *			$fields[n][3] or $fields[n]['foreign_index']	PK of the referenced table
	 *
	 *	@param	string	$table
	 *	@param	array	$fields
	 *	@param	mixed	$primaryKey
	 *	@param	array	$foreignKeys
	 */
	public function createTable(string $table, array $fields, $primaryKey = null, array $foreignKeys = null)
	{
		$sql = ' CREATE TABLE ' . $table . '( ';
		$fieldren = [];

		if (is_string($primaryKey))
		{
			$fieldren[$primaryKey] = ' ' . $primaryKey . ' int not null auto_increment primary key ';
		}
		elseif (is_array($primaryKey))
		{
			$name = ($primaryKey['name'] ?? $primaryKey[0] ?? 'id');
			$fieldren[$name] = ' ' . $name .
				' ' . $this->dataTypes(
						$primaryKey['type'] ?? $primaryKey[1] ?? 'int',
						$primaryKey['length'] ?? $primaryKey[2] ?? null,
						$primaryKey['precision'] ?? $primaryKey[3] ?? null
					) .
				' not null ' .
				' ' . ( ($primaryKey['auto_increment'] ?? $primaryKey[4] ?? true) ? 'auto_increment' : '' ) .
				' primary key ';
		}
		else
		{
			$fieldren['id'] = ' id int not null auto_increment primary key ';
		}

		foreach ($fields as $n => $field)
		{
			if (is_string($n))
			{
				$fieldren[$n] = ' ' . $n .
					' ' . $this->dataTypes($field['type'] ?? $field[1] ?? 'int',
							$field['length'] ?? $field[2] ?? null,
							$field['precision'] ?? $field[3] ?? null
						) .
					' ' . ( ($field['nullable'] ?? $field[4] ?? true) ? 'null' : 'not null' );
			}
			else
			{
				$name = ($field['name'] ?? $field[0] ?? 'id');
				$fieldren[$name] = ' ' . $name .
					' ' . $this->dataTypes($field['type'] ?? $field[1] ?? 'int',
							$field['length'] ?? $field[2] ?? null,
							$field['precision'] ?? $field[3] ?? null
						) .
					' ' . ( ($field['nullable'] ?? $field[4] ?? true) ? 'null' : 'not null' );
			}
		}

		if (!empty($foreignKeys))
		{
			$fkn = 1;
			foreach ($foreignKeys as $n => $field)
			{
				if (is_string($n))
				{
					$fieldren[$n] = ' constraint fk_' . $table . '_' . 
						($field['name'] ?? $field[0] ?? 'id') . '_' . $fkn .
						' foreign key ' .
						' ' . ($field['key'] ?? $field[1]) .
						' references ' .
						' ' . ($field['foreign_table'] ?? $field[2]) .
						' ( ' . ($field['foreign_index'] ?? $field[3] ?? 'id') . ')';
				}
				else
				{
					$name = ($field['name'] ?? $field[0] ?? 'id');
					$fieldren[$n] = ' constraint fk_' . $table . '_' . $name . '_' . $fkn .
						' foreign key ' .
						' ' . ($field['key'] ?? $field[1]) .
						' references ' .
						' ' . ($field['foreign_table'] ?? $field[2]) .
						' ( ' . ($field['foreign_index'] ?? $field[3] ?? 'id') . ')';
				}
			}
		}

		$sql .= implode(', ', $fieldren) . ');';

		return $sql;
	}

}

