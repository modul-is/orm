# Licence

This repository is overhaul of [YetORM](https://github.com/uestla/YetORM) under MIT licence. Original fork [kravcik/core](https://github.com/kravcik/core) will be not maintained for Nette3. You can find unfinished PR for YetORM and Nette 3 [here](https://github.com/uestla/YetORM/pull/23).

# Idea
This is hybrid for simple scalable using database layer with ORM principes. We using entites like data object for structured work (etc. forms, details, routing) and also is possible with repository call `$repository->getTable()` and inject it in Ublaboo/Datagrid. For some edge-cases we using `$repository->query()` with custom SQL.

# Extra types
* **json** - save array and load array like json
* **date** - save and load `\Nette\Utils\DateTime`

