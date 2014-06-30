ScoreSql
========

A SQL builder component, that is easy to use.

*   Uses named placeholder as default (well, no other choice),
*   tested against MySql and PostgreSql.


### license

MIT License


Basic Usage
-----------

### construction

use ```Factory``` class to get the query object, with
optional parameter to select the database type.

```php
$query = Factory::query( 'mysql' );
```

### select statement

```php
$sqlStatement = $query
    ->table('myTable')
    ->column('col1', 'aliased1')
    ->columns( 'col2', 'col3')
    ->where( Where::column('status')->is('1') )
    ->select();
```

Use ```Where::column('name')``` methods to start where clause.
 for shorthand notation, use ```$query->var_name``` to start
 where clause as well.

The construction of where clause can be easy;
 Specifying the column as ```$query->var_name```, then
 continued with the condition, such as ```is()```, ```in()```,
 ```lessThan()```, etc.

the resulting $sqlStatement will look like:

```sql
SELECT "col1" AS "aliased1", "col2", "col3" FROM "myTable" WHERE "status" = :db_prep_1
```

### insert statement

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

### update statement

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

### complex where clause examples

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

Another example uses ```Where``` class to generate ```$where```
 object. ```open/close``` methods constructs another ```Where```
 object to create parenthesis.


```php
$query->table('table')->where(
    Where::bracket()
        ->gender->is('F')->or()->status->is('1')
    ->close()
    ->open()
        ->gender->is('M')->or()->status->is('2')
    ->close()
)
->select();
```

this will builds sql like:

```sql
SELECT * FROM "table" WHERE
    ( "gender" = :db_prep_1 OR "status" = :db_prep_2 ) AND
    ( "gender" = :db_prep_3 OR "status" = :db_prep_4 )
ORDER BY "id" ASC LIMIT :db_prep_5
```



Join
----

Pass ```Join``` object to ```join``` method to construct
 table join.

Some examples:


```php
$found2 = $query
    ->table( 'dao_user', 'u1' )
    ->join( Join::table( 'dao_user', 'u2' )->using( 'status' ) )
    ->where( $query->user_id->is(1) )
    ->select();
```

will produce,

```sql
SELECT *
    FROM `dao_user` `u1`
        JOIN `dao_user` `u2` USING( `status` )
    WHERE `u1`.`user_id` = :db_prep_1
```

and this will produce,

```php
$found = $query
    ->table( 'dao_user', 'u1' )
    ->join(
        Join::left( 'dao_user', 'u2' )
            ->on( $query->status->identical( 'u1.status' ) )
    )
    ->where( $query->user_id->is(1) )
    ->select();
```

the following sql statement.

```sql
SELECT *
    FROM `dao_user` `u1`
        LEFT OUTER JOIN `dao_user` `u2` ON ( `u2`.`status` = `u1`.`status` )
    WHERE `u1`.`user_id` = :db_prep_1
```


History
-------

it was originally developed in WScore.Basic repository, then
 moved to WScore.DbAccess repository, and now it has its own
 repository, WScore.SqlBuilder.

Hopefully, this will be the last move...