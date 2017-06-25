dbObject - model implementation on top of the MysqliDb.

Please note that this library is not pretending to be a full stack ORM, but simply an OOP wrapper for `mysqlidb`.

<hr>
###Initialization

Include mysqlidb and dbObject classes. If you want to use model autoloading instead of manually including them in the scripts use `autoload()` method.
```php
require_once("libs/MysqliDb.php");
require_once("libs/dbObject.php");

// db instance
$db = new Mysqlidb('localhost', 'user', '', 'testdb');
// enable class autoloading
dbObject::autoload("models");
```

Each database table could be easily mapped into a dbObject instance.  If you do not want to create model for a simple table its object could be simply created with a `table()` method.
```php
$user = dbObject::table("users");
```

Otherwise basic model should be declared as:
```php
class user extends dbObject {}
```
In case autoload is set to 'models' directory, the filename should be models/user.php

Class will be related to 'user' table. To change the table name, define correct name in the `$dbTable` variable:

```php
    protected $dbTable = "users";
```

Both objects created throw new class file creation of with `table()` method will have the same set of methods available. Only exception is that relations, validation or custom model methods
will not be working with an objects created with `table()` method.


###Selects
Retrieving objects from the database is pretty much the same process as a mysqliDb `get()`/`getOne()` methods without a need to specify table name. All mysqlidb functions like `where()`, `orWhere()`, `orderBy()`, `join()`, etc. are supported.

##Retrieving All Records

```php
//$users = dbObject::table('users')->get();
$users = user::get();
foreach ($users as $u) {
  echo $u->login;
}
```

## Using Where Condition And A Limit
```php
$users = user::where("login", "demo")->get(Array (10, 20));
foreach ($users as $u) ...
```

##Retrieving A Model By Primary Key

```php
//$user = dbObject::table('users')->byId(1);
$user = user::byId(1);
echo $user->login;
```

dbObject will also assume that each table has a primary key column named "id". You may define a primaryKey property to override this assumption.

```php
  protected $primaryKey = "userId";
```


###Insert Row
1. OOP Way. Just create new object of a needed class, fill it in and call `save()` method. Save will return
record id in case of success and false in case if insert will fail.
```php
//$user = dbObject::table('users');
$user = new user;
$user->login = 'demo';
$user->password = 'demo';
$id = $user->save();
if ($id)
  echo "user created with id = " . $id;
```

2. Using arrays
```php
$data = Array('login' => 'demo',
        'password' => 'demo');
$user = new user ($data);
$id = $user->save();
if ($id == null) {
    print_r($user->errors);
    echo $db->getLastError;
} else
    echo "user created with id = " . $id;
```

3. Multisave

```php
$user = new user;
$user->login = 'demo';
$user->pass = 'demo';

$p = new product;
$p->title = "Apples";
$p->price = 0.5;
$p->seller = $user;
$p->save();
```

After `save()` is called, both new objects (user and product) will be saved.


###Update
To update model properties just set them and call `save()` method. Values that need to be changed could be passed as an array to the `save()` method as well.

```php
$user = user::byId(1);
$user->password = 'demo2';
$user->save();
```
```php
$data = Array('password', 'demo2');
$user = user::byId(1);
$user->save($data);
```

###Delete
Use `delete()` method on any loaded object.
```php
$user = user::byId(1);
$user->delete();
```

###Relations
Currently dbObject supports only `hasMany` and `hasOne` relations. To use them declare `$relations` array in the model class.
After that you can get related object via variable names defined as keys.

##hasOne example:
```php
    protected $relations = Array(
        'person' => Array("hasOne", "person", 'id');
    );

    ...

    $user = user::byId(1);
    // sql: select * from users where id = $personValue
    echo $user->person->firstName . " " . $user->person->lastName . " have the following products:\n";
    // one more sql: select * from person where id=x
```
Please note, that following way of querying will execute 2 sql queries:
1. `select * from users where id=1`
2. `select * from person where id=x`

To optimize this into single select join query use `with()` method.
```php
   $user = user::with('person')->byId(1);
   // sql: select * from users left join person on person.id = users.id wher id = 1;
    echo $user->person->firstName . " " . $user->person->lastName . " have the following products:\n";
```

##hasMany example:
In the `hasMany` array should be defined the target object name (product in example) and a relation key (userid).
```php
    protected $relations = Array(
        'products' => Array("hasMany", "product", 'userid')
    );

    ...

    $user = user::byId(1);
    // sql: select * from $product_table where userid = $userPrimaryKey
    foreach ($user->products as $p) {
            echo $p->title;
    }
```

### Joining tables
```php
$depts = product::join('user');
$depts = product::join('user', 'productid');
```

First parameter will set an object which should be joined. Second paramter will define a key. Default key is `$objectName+'Id'`


NOTE: Objects returned with `join()` will not save changes to a joined properties. For this you can use relationships.

###Timestamps
Library provides a transparent way to set timestamps of an object creation and its modification:
To enable that define `$timestamps` array as follows:
```php
protected $timestamps = Array ('createdAt', 'updatedAt');
```
Field names can't be changed.

###Array Fields
dbObject can automatically handle array type of values. Optionaly you can store arrays in json encoded or in pipe delimited format.
To enable automatic json serialization of the field define `$jsonFields` array in your modal:
```php
    protected $jsonFields = Array('options');
```
To enable pipe delimited storage of the field, define `$arrayFields` array in your modal:
```php
    protected $arrayFields = Array('sections');
```
The following code will now store `'options'` variable as a json string in the database, and will return an array on load.
Same with the `'sections'` variable except that it will be stored in pipe delimited format.
```php
    $user = new user;
    $user->login = 'admin';
    $user->options = Array('canReadNews', 'canPostNews', 'canDeleteNews');
    $user->sections = Array('news', 'companyNews');
    $user->save();
    ...
    $user = user::byId(1);
    print_r($user->options);
```

###Validation and Error checking
Before saving and updating the row, dbObject does input validation. In case validation rules are set but their criteria is
not met, then `save()` will return an error with its description. For example:
```php
$id = $user->save();
if (!$id) {
    // show all validation errors
    print_r($user->errors);
    echo $db->getLastQuery();
    echo $db->getLastError();
}
echo "user were created with id" . $id;
```
Validation rules must be defined in `$dbFields` array.
```php
  protected $dbFields = Array(
    'login' => Array('text', 'required'),
    'password' => Array('text'),
    'createdAt' => Array('datetime'),
    'updatedAt' => Array('datetime'),
    'custom' => Array('/^test/'),
  );
```
First parameter is a field type. Types could be the one of following: text, bool, int, datetime or a custom regexp.
Second parameter is 'required' and its defines that following entry field be always defined.

**NOTE:** All variables which are not defined in the `$dbFields` array will be ignored from insert/update statement.

###Using array as a return value
dbObject can return its data as array instead of object. To do that, the `ArrayBuilder()` function should be used in the beginning of the call.
```php
    $user = user::ArrayBuilder()->byId(1);
    echo $user['login'];

    $users = user::ArrayBuilder()->orderBy("id", "desc")->get();
    foreach ($users as $u)
        echo $u['login'];
```

The following call will return data only of the called instance without any relations data. Use `with()` function to include relation data as well.
```php
    $user = user::ArrayBuilder()->with("product")->byId(1);
    print_r ($user['products']);
```

###Using json as a return value
Together with `ArrayBuilder()` and `ObjectBuilder()`, dbObject can also return a result in json format to avoid extra coding.
```php
    $userjson = user::JsonBuilder()->with("product")->byId(1);
```
###Object serialization

Object could be easily converted to a json string or an array.

```php
    $user = user::byId(1);
    // echo will display json representation of an object
    echo $user;
    // userJson will contain json representation of an object
    $userJson = $user->toJson();
    // userArray will contain array representation of an object
    $userArray = $user->toArray();
```

###Pagination
Use paginate() instead of get() to fetch paginated result
```php
$page = 1;
// set page limit to 2 results per page. 20 by default
product::$pageLimit = 2;
$products = product::arraybuilder()->paginate($page);
echo "showing $page out of " . product::$totalPages;

```
###Examples

Please look for a use examples in <a href='tests/dbObjectTests.php'>tests file</a> and test models inside the <a href='tests/models/'>test models</a> directory
