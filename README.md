WScore.SqlBuilder
=================

SQL Builder component. 

Uses named placeholder as default (well, no other choice).

### license

MIT License


Usage
-----

### construction

use ```Factory``` class to get the query object, with
optional parameter to select the database type.

```php
$query = Factory::query( 'mysql' );
```

### simple select statement

```php
$sqlStatement = $query
    ->table('myTable')
    ->column('col1', 'col2')
    ->where(
        $query->status->eq('1')
    )
    ->select();
```

### simple insert statement

```php
$sqlStatement = $query
    ->table('myTable')
    ->insert( [ 'col1' => 'val1', 'col2'=>'val2' ] );
```

### simple update statement

```php
$sqlStatement = $query
    ->table('myTable')
    ->where(
        $query->name->like('bob')->or()->status->eq('1')
    )
    ->update( [
        'date' => Query::raw('NOW()'),
        'col2'=>'val2'
    ] );
```

### getting the bound value

use ```getBind``` method to retrieve the bound value for
prepared statement as follows.

```php
$sqlStatement = ...
$bindValues = $query->getBind();
$stmt = $pdo->prepare( $sqlStatement );
$stmt->execute( $bindValues );
```

Advanced SQL
------------

### complex where clause

```php
echo $query->table('tab')->where(
    $query->name->startWith('A')->gender->eq('M')
)->whereOr(
    $query->name->startWith('B')->gender->eq('F')
)
;
```

will outputs (something like...)

```sql
SELECT * FROM "tab" WHERE ( "name" LIKE 'A%' AND "gender"=:db_prep_1 ) OR ( "name" LIKE 'B%' AND "gender"=:db_prep_2 )
```

### join

not implemented yet.


History
-------

it was originally developed in WScore.Basic repository, then
 moved to WScore.DbAccess repository, and now it has its own
 repository, WScore.SqlBuilder.

Hopefully, this will be the last move...