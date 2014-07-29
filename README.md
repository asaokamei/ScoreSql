ScoreSql
========

A simple and easy SQL builder component.

The objective is to make the construction of SQL statements
 as easy and intuitive as possible; even for the complex SQL
 statements with sub-queries and complex OR conditions.

*   Uses named placeholder as default (well, no other choice),
*   tested against MySql and PostgreSql.

CURRENT STATUS: Beta.

i.e. the API is still under design.


### license

MIT License


Basic Usage
-----------

### construction

use ```DB``` class to get the query object, with
optional parameter to select the database type.

```php
$query = DB::db( 'mysql' )->from( 'myTable' );
// omitting connect returns standard SQL builder.
$query = DB::from( 'thisTable' );
```

### select statement

```php
$sqlStatement = DB::from('myTable')
    ->column('col1', 'aliased1')
    ->columns( 'col2', 'col3')
    ->filter( DB::given('status')->is('1') )
    ->select();
```

Use ```DB::given()``` methods to start where clause.
 for shorthand notation, use ```$query->var_name``` to start
 where clause as well. as such,

```php
DB::from('myTable')
    ->column('col1', 'aliased1')
    ->columns( 'col1', 'col2' )
    ->filter( $query->status->is(1) )
    ->select();
```

the resulting $sqlStatement will look like:

```sql
SELECT "col1" AS "aliased1", "col2", "col3" FROM "myTable" WHERE "status" = :db_prep_1
```

### insert statement

```php
$sqlStatement = DB::from('myTable')
    ->insert( [ 'col1' => 'val1', 'col2'=>'val2' ] );
```

or, this also works.

```php
$query->col1 = 'val1';
$query->col2 = 'val2';
$sqlStatement = DB::from('myTable')->insert();
```

both cases will generate sql like:

```sql
INSERT INTO "myTable" ( "col1", "col2" ) VALUES ( :db_prep_1, :db_prep_2 )
```

### update statement

```php
$sqlStatement = DB::from('myTable')
    ->filter(
        DB::given('name')->like('bob')->or()->status->eq('1')
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
$sqlStatement = DB::from('myTable')->update();
```

will generate update SQL like:

```sql
UPDATE "myTable" SET
    "date"=NOW(),
    "col2"=:db_prep_1
WHERE "name" LIKE :db_prep_2 OR "status" = :db_prep_3
```

### getting the bound values

use ```getBind()``` method to retrieve the bound values for
prepared statement as follows.

```php
$bindValues = $query->getBind();
```

If you start query with ```Query```, use ```DB::bind()```
 method to get the bound values.

as such,

```php
$sqlStatement = DB::from()... // construct SQL statement.
$bindValues   = DB::bind();   // get the binding values from last query.
$stmt = $pdo->prepare( $sqlStatement );
$stmt->execute( $bindValues );
```


Complex Conditions
------------------

### or conditions

Use ```filterOr( $where )``` method to construct a OR
 in the where statement.

```php
echo DB::from('tab')
    ->filter(
        DB::given('name')->startWith('A')->gender->eq('M')
    )->filterOr(
        DB::given('name')->startWith('B')->gender->eq('F')
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
echo DB::from('table')
    ->filter(
        DB::given('gender')->is('F')->or()->status->is('1')
    )->filter(
        DB::given('gender')->is('M')->or()->status->is('2')
    )
    ->select();

// alternative way of writing the same sql.
echo DB::from('table')
    ->filter(
        DB::bracket()
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

### having clause

to-be-written


Join Clause
-----------

To construct table join, use ```DB::join``` method
 to start join clause (which is a Join object).

### join using

examples:

```php
$found2 = DB::from( 'dao_user', 'u1' )
    ->join( DB::join( 'dao_user', 'u2' )->using( 'status' ) )
    ->filter( DB::given('user_id')->is(1) )
    ->select();
```

will produce,

```sql
SELECT *
    FROM `dao_user` `u1`
        JOIN `dao_user` `u2` USING( `status` )
    WHERE `u1`.`user_id` = :db_prep_1
```

### join on

Meanwhile, the following PHP code,

```php
$found = DB::from( 'dao_user', 'u1' )
    ->join(
        DB::join( 'dao_user', 'u2' )->left()
            ->on( DB::given('status')->identical( 'u1.status' ) )
    )
    ->filter( DB::given()->user_id->is(1) )
    ->select();
```

, will produce the sql statement as shown below.

```sql
SELECT *
    FROM `dao_user` `u1`
        LEFT OUTER JOIN `dao_user` `u2` ON ( `u2`.`status` = `u1`.`status` )
    WHERE `u1`.`user_id` = :db_prep_1
```


Sub Queries
-----------

Sub queries is implemented for several cases but are not
 tested against real databases, yet.

### sub query in column

```php
$query = DB::from( 'main' )
    ->column(
        DB::subQuery('sub')
            ->column( DB::raw('COUNT(*)'), 'count' )
            ->where( DB::given('status')->identical('$.status') ),
        'count_sub'
    );
```

will generate the following sql.

```sql
SELECT ( SELECT COUNT(*) AS "count" FROM "sub" AS "sub_1" WHERE "sub_1"."status" = "main"."status" )
AS "count_sub" FROM "main"
```

### sub query as table

```php
$query = DB::from( DB::subQuery('sub')->where( DB::given('status')->is(1)) )
    ->where(
        DB::given('name')->is('bob')
    );
```

```sql
SELECT * FROM
    ( SELECT * FROM "sub" AS "sub_1" WHERE "sub_1"."status" = :db_prep_1 )
WHERE "name" = :db_prep_2'
```

### sub query in update and insert

```php
DB::from( 'main' )
    ->value( 'count', DB::subQuery('sub')
                        ->column( DB::raw('COUNT(*)') )
                        ->where( DB::given('status')->is(1) )
    )
    ->toUpdate();
```

```sql
UPDATE "main" SET "count"=( SELECT COUNT(*) FROM "sub" AS "sub_1" WHERE "sub_1"."status" = :db_prep_1 )
```

```php
DB::from( 'main' )
    ->value( 'count', DB::subQuery('sub')
                        ->column(DB::raw('COUNT(*)'))
                        ->where( DB::given('status')->is(1) )
    )
    ->toInsert();
```

```sql
INSERT INTO "main" ( "count" ) VALUES ( ( SELECT COUNT(*) FROM "sub" AS "sub_1" WHERE "sub_1"."status" = :db_prep_1 ) )
```


History
-------

it was originally developed in WScore.Basic repository, then
 moved to WScore.DbAccess repository, and now it has its own
 repository, WScore.SqlBuilder.

Hopefully, this will be the last move...