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
        $query->status->is('1')
    )
    ->select();
```

Use ```where( $where )``` methods to set where clause.

The construction of where clause can be easy;
 Specifying the column as ```$query->var_name```, then
 continued with the condition, such as ```is()```, ```in()```,
 ```lessThan()```, etc.

the resulting $sqlStatement will look like:

```sql
SELECT "col1" AS "col2" FROM "myTable" WHERE "status" = :db_prep_1
```

### simple insert statement

```php
$sqlStatement = $query
    ->table('myTable')
    ->insert( [ 'col1' => 'val1', 'col2'=>'val2' ] );
```

or, this also works.

```php
$query->col1 = 'val1';
$query->col2 = 'val2';
$sqlStatement = $query->table('myTable')->insert();
```

both cases will generate sql like:

```sql
INSERT INTO "myTable" ( "col1", "col2" ) VALUES ( :db_prep_1, :db_prep_2 )
```

### simple update statement

```php
$sqlStatement = $query
    ->table('myTable')
    ->where(
        $query->name->like('bob')->or()->status->eq('1')
    )
    ->update( [
        'date' => $query->raw('NOW()'),
        'col2'=>'val2'
    ] );
```

or, this also works.

```php
$query->date = $query->raw('NOW()');
$query->col2 = 'val2';
$sqlStatement = $query->table('myTable')->update();
```

will generate update SQL like:

```sql
UPDATE "myTable" SET
    "date"=NOW(),
    "col2"=:db_prep_1
WHERE "name" LIKE :db_prep_2 OR "status" = :db_prep_3
```

### getting the bound value

use ```getBind()``` method to retrieve the bound value for
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

Use ```whereOr( $where )``` method to construct a OR
 in the where statement.

```php
echo $query->table('tab')
->where(
    $query->name->startWith('A')->gender->eq('M')
)->whereOr(
    $query->name->startWith('B')->gender->eq('F')
);
```

this will builds sql like:

```sql
SELECT * FROM "tab" WHERE
( "name" LIKE 'A%' AND "gender"=:db_prep_1 ) OR
( "name" LIKE 'B%' AND "gender"=:db_prep_2 )
```

another way of building where clause is to use ```filter()```.
 This method allows to construct where clause continuously,
 as```filter()->var_name->``` will set the
 column for filtering. make sure to indicate the end of
 filtering with ```end()``` method.

```php
$query->table('table')->filter()
    ->openBracket()
        ->gender->is('F')->or()->status->is('1')
    ->closeBracket()
    ->openBracket()
        ->gender->is('M')->or()->status->is('2')
    ->closeBracket()
->end()
->select();
```

this will builds sql like:

```sql
SELECT * FROM "table" WHERE
    ( "gender" = :db_prep_1 OR "status" = :db_prep_2 ) AND
    ( "gender" = :db_prep_3 OR "status" = :db_prep_4 )
ORDER BY "id" ASC LIMIT :db_prep_5
```


### join

it is implemented now, but not really tested.


History
-------

it was originally developed in WScore.Basic repository, then
 moved to WScore.DbAccess repository, and now it has its own
 repository, WScore.SqlBuilder.

Hopefully, this will be the last move...