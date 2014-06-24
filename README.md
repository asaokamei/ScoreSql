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
    ->filter(
        $query->status->is('1')
    )
    ->select();
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

or, this also works.

```php
$query->col1 = 'val1';
$query->col2 = 'val2';
$sqlStatement = $query->table('myTable')->update();
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

Use ```filter( $where )``` methods to set where clause;
 The using ```$query->var_name``` will start constructing the
 where clause.

```php
echo $query->table('tab')->filter(
    $query->name->startWith('A')->gender->eq('M')
)->whereOr(
    $query->name->startWith('B')->gender->eq('F')
)
;
```

this will builds sql like:

```sql
SELECT * FROM "tab" WHERE
( "name" LIKE 'A%' AND "gender"=:db_prep_1 ) OR ( "name" LIKE 'B%' AND "gender"=:db_prep_2 )
```

another way of using ```filter()``` method is continue to the
 condition using ```filter()->var_name->``` using ```__get()```
 method. constructing of the where clause will start, and
 ends at ```end()``` method.

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