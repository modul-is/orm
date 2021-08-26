# Licence

This repository is an overhaul of [YetORM](https://github.com/uestla/YetORM) under MIT licence. Original fork [kravcik/core](https://github.com/kravcik/core) will not be maintained for Nette3. You can find the unfinished PR for YetORM and Nette 3 [here](https://github.com/uestla/YetORM/pull/23).

# Abstract
This is a hybrid of a simple scalable database layer with ORM principles. We are using entities as data objects for structured work (etc. forms, details, routing). It is also possible to make a repository call `$repository->getTable()` and inject it into Ublaboo/Datagrid. For some edge-cases we are using `$repository->query()` with custom SQL.

# Special types & behavior
* **array** - stored in database as json
* **\Nette\Utils\DateTime** - save and load `\Nette\Utils\DateTime`

Input | Entity | Nullable | Output |
:----:|:------:|:--------:|:------:|
int(1) | int | &check; &#124;&#124; &cross; | int(1) |
int(0) | int | &check; &#124;&#124; &cross; | int(0) |
float(0.0) | int | &check; &#124;&#124; &cross; | Exception |
string("") | int | &check; &#124;&#124; &cross; | Exception |
float(1.0) | float | &check; &#124;&#124; &cross; | float(1.0) |
float(0.0) | float | &check; &#124;&#124; &cross; | float(0.0) |
int(0) | float | &check; &#124;&#124; &cross; | Exception |
string("") | float | &check; &#124;&#124; &cross; | Exception |
string("a") | string | &check; &#124;&#124; &cross; | string("a") |
string("") | string | &check; &#124;&#124; &cross; | string("") |
int(0) | string | &check; &#124;&#124; &cross; | Exception |
float(0.0) | string | &check; &#124;&#124; &cross; | Exception |
array(\["a" => "b"\]) | array | &check; &#124;&#124; &cross; | string("{"a":"b"}") |
array(\[\]) | array | &check; &#124;&#124; &cross; | string("[]") |
string("{"a":"b"}") | array | &check; &#124;&#124; &cross; | Exception |
string("[]") | array | &check; &#124;&#124; &cross; | Exception |
DateTime("2021-01-01 12:34:56") | DateTime | &check; &#124;&#124; &cross; | string("2021-01-01 12:34:56") |
string("2021-01-01 12:34:56") | DateTime | &check; &#124;&#124; &cross; | Exception |
string("a") | DateTime | &check; &#124;&#124; &cross; | Exception |
string("") | DateTime | &check; &#124;&#124; &cross; | Exception |
bool(false) | int &#124;&#124; float &#124;&#124; string &#124;&#124; array &#124;&#124; DateTime | &check; &#124;&#124; &cross; | Exception |
null | int &#124;&#124; float &#124;&#124; string &#124;&#124; array &#124;&#124; DateTime | &check; | null |
null | int &#124;&#124; float &#124;&#124; string &#124;&#124; array &#124;&#124; DateTime | &cross; | Exception |
bool sloupec?