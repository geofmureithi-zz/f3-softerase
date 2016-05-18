# F3 SoftErase

Instead of removing records from your database, the SoftErase Trait will store records in a trashed state, and deleted records can be restored.

##System Requirements

SoftErase requires at least Fat-Free v3.* and PHP 5.4+.
It is also required to have a `deleted_at` field for all SQL databases

## Installation

To install, copy the `/lib/db/softerase.php` file to the `/lib/db/`. 
OR use Composer and run `composer require acemureithi/f3-softerase:1.*`

## Getting Started

- Define your class

```php
class Book extends \DB\SQL\Mapper{ //Also Works for JIG not tested on Mongo
	use \DB\SoftErase; // The SoftErase Trait
}
```
- Use it!

```php
$db = new \DB\SQL('mysql:host=localhost;port=3306;dbname={db}', 'username', 'password');
$table = 'books';
$mapper = new Book($mysql, $table);
$mapper->book = "The day of the jackal";
$mapper->author = "Cant Remember";
$mapper->save();
$mapper->erase(); //"Record was soft erased"
$mapper->restore(); //Record is back!
$mapper->forceErase(); //Okay Goodbye We cannot restore you anymore
```
And thats it!

## SoftErase API

### deleteTime 
String
The Field to store the time of erasing.(Timestamp)

### bypass
bool
Whether to bypass softerase. Set true and records are deleted permanently

### notDeletedFilter()
Get the (not Deleted) Filter.
`@return array|null`

### forceErase()
Force a hard (normal) erase.
`@return bool`
```php
$mapper->load(array('title = ?','50 Shades'));
$mapper->forceErase(); //hard Erase
```

### erase()
Perform a soft erase.
`@return bool`
```php
$mapper->load(array('title = ?','50 Shades'));
$mapper->erase(); //Record not permanently deleted but does not show up in find() and load()
```

### restore()
Restore a soft-erased record.
`@return bool|null`
```php
$mapper->load(array('title = ?','50 Shades'));
$mapper->erase(); //Record not permanently deleted but does not show up in find() and load()
$mapper->restore(); //Restores the record now record is in find() and load()
```
### erased()
Determine if the instance has been soft-deleted.
`@return bool`
```php
$mapper->load(array('title = ?','50 Shades'));
$mapper->erase(); //Record not permanently deleted but does not show up in find() and load()
$mapper->erased(); //True
```
### SoftErase::onlyErased($mapper)
Cursor instance that includes only soft erases.
`@return array`
```php
Books::onlyErased($mapper);
```
#### NOTE
Also note that load(), erase(), save() and find() are defined in the trait

## FAQ




