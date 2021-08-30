# Licence
This repository is an overhaul of [YetORM](https://github.com/uestla/YetORM) under MIT licence. Original fork [kravcik/core](https://github.com/kravcik/core) will not be maintained for Nette3. You can find the unfinished PR for YetORM and Nette 3 [here](https://github.com/uestla/YetORM/pull/23).

# Abstract
This is a hybrid of a simple scalable database layer with ORM principles.

We are using entities as data objects for structured work (forms, details, routing, etc.).

```
class TestEntity extends \ModulIS\Entity
{
	public int $a;

	public ?string $b;

	public \Nette\Utils\DateTime $c;
}
```

It is also possible to make a repository call `$repository->getTable()` and inject it into Ublaboo/Datagrid.

```
$grid = new \Ublaboo\DataGrid\DataGrid;

$grid->setDataSource($this->TestRepository->getTable()
	->select('a, b')
	->where('b IS NOT NULL')
	->fetchAll());
```

For some edge-cases we are using `$repository->query()` with custom SQL.

```
$this->TestRepository->query("SELECT `c` FROM `test` WHERE (`b` IS NOT NULL)")->fetch();
```

## Readonly
The Readonly attribute can be used for properties that should not be written into, for example columns with auto increment.

```
#[\ModulIS\Readonly]
public int $id;
```

## Special types & behavior
* **array** - stored in database as json
* **bool** - stored in database as int
* **\Nette\Utils\DateTime** - save and load `\Nette\Utils\DateTime`

Input | Entity | Nullable | Output |
:----:|:------:|:--------:|:------:|
int(1) | int | &check; &#124;&#124; &cross; | int(1) |
int(0) | int | &check; &#124;&#124; &cross; | int(0) |
string("0") | int | &check; &#124;&#124; &cross; | Exception |
float(1.0) | float | &check; &#124;&#124; &cross; | float(1.0) |
float(0.0) | float | &check; &#124;&#124; &cross; | float(0.0) |
string("0.0") | float | &check; &#124;&#124; &cross; | Exception |
string("a") | string | &check; &#124;&#124; &cross; | string("a") |
string("") | string | &check; &#124;&#124; &cross; | string("") |
array(\["a" => "b"\]) | array | &check; &#124;&#124; &cross; | string("{"a":"b"}") |
array(\[\]) | array | &check; &#124;&#124; &cross; | string("[]") |
string("{"a":"b"}") | array | &check; &#124;&#124; &cross; | Exception |
string("[]") | array | &check; &#124;&#124; &cross; | Exception |
bool(false) | bool | &check; &#124;&#124; &cross; | bool(false) |
string("false") | bool | &check; &#124;&#124; &cross; | Exception |
DateTime("2021-01-01 12:34:56") | DateTime | &check; &#124;&#124; &cross; | string("2021-01-01 12:34:56") |
string("2021-01-01 12:34:56") | DateTime | &check; &#124;&#124; &cross; | Exception |
int(0) | float &#124;&#124; string &#124;&#124; array &#124;&#124; bool &#124;&#124; DateTime | &check; &#124;&#124; &cross; | Exception |
float(0.0) | int &#124;&#124; string &#124;&#124; array &#124;&#124; bool &#124;&#124; DateTime | &check; &#124;&#124; &cross; | Exception |
string("") | int &#124;&#124; float &#124;&#124; array &#124;&#124; bool &#124;&#124; DateTime | &check; &#124;&#124; &cross; | Exception |
array(\[\]) | int &#124;&#124; float &#124;&#124; string &#124;&#124; bool &#124;&#124; DateTime | &check; &#124;&#124; &cross; | Exception |
bool(false) | int &#124;&#124; float &#124;&#124; string &#124;&#124; array &#124;&#124; DateTime | &check; &#124;&#124; &cross; | Exception |
null | int &#124;&#124; float &#124;&#124; string &#124;&#124; array &#124;&#124; bool &#124;&#124; DateTime | &check; | null |
null | int &#124;&#124; float &#124;&#124; string &#124;&#124; array &#124;&#124; bool &#124;&#124; DateTime | &cross; | Exception |

## Custom types
You can also use custom types, you just have to create a class that extends `\ModulIS\Datatype\Datatype` and implements both of its static functions:
1) `input($value)` - save logic (conversion into a database-compatible type)
2) `output($value)` - read logic (conversion back into the original type)

```
class File extends \ModulIS\Datatype\Datatype
{
	public static function input($value): string
	{
		if($value instanceof \SplFileInfo)
		{
			$value = $value->getPathname();
		}
		else
		{
			throw new \ModulIS\Exception\InvalidArgumentException("Instance of '\SplFileInfo' expected, '" . get_debug_type($value) . "' given.");
		}

		return $value;
	}


	public static function output($value): self
	{
		$value = new self(new \SplFileInfo($value));

		return $value;
	}
}
```

Then you can use your type with a property just like all the usual types.

```
public \App\Datatype\File $file;
```

Just make sure the data is always wrapped in the specified class to avoid errors.

```
$entity->file = new \App\Datatype\File(new \SplFileInfo('../app/Datatype/File.php'));

bdump($entity->file); //App\Datatype\File(value: SplFileInfo(path: '../app/Datatype/File.php'));
bdump($ntity->file->value->getFilename()); //'File.php'
```
