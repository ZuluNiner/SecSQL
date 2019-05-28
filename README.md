# SecSQL
### A secure and easy way to access SQL via PHP PDO

#### Require the package in PHP
###### Can also be done through including/requiring composer autoload.php
```php
<?php
  require_once("../secsql.php");
  use ZuluNiner\SecSQL;
```

### SECSQL\Query
##### params in order left to right
###### Required
* $hostname
* $port
* $username
* $password
* $database

  ###### Not required: Defaults listed
* $driver = 'mysql'
* $charset = "utf8"
```php
$SecSQL = new SecSQL("localhost","3306","username","password","database");
```
### ZuluNiner\SECSQL->Select()
#### params in order left to right
	
###### Required
* $table

###### Not required: Defaults listed
* $columns = null
	
```php
$select = $query->Select("users")->Execute();
$select = $query->Select("users",['username'])->Execute(); //Retreives only the username
$select = $query->Select("users",['username','pass_hash'])->Execute(); //Retreives only the username and pass_hash
```

### ZuluNiner\SECSQL->Insert()
#### params in order left to right
	
###### Required
* $table
* $columnValues
```php
$insert = $query->Insert("users",["username"=>"test","pass_hash"=>password_hash("something",PASSWORD_BCRYPT)])->Execute();
```

### ZuluNiner\SECSQL->Update()
#### params in order left to right
	
###### Required
* $table
* $columnValues

```php
$update = $query->Update("users",["username"=>"test2","pass_hash"=>password_hash("newsomething",PASSWORD_BCRYPT)])->Execute();
```

### ZuluNiner\SECSQL->Where()
#### params in order left to right
	
###### Required
* $table
* $columnValues

```php
$update = $query->Update("users",["username"=>"test2"])->Where(["username","=","test"])->Execute();
$update = $query->Update("users",["username"=>"test2"])->Where(["username","LIKE","%test%"])->Execute();
$update = $query->Update("users",["username"=>"test2"])->Where([["username","=","test","AND"],["id","=",1]])->Execute();
$update = $query->Update("users",["username"=>"test2"])->Where([["username","=","test","OR"],["id","=",1]])->Execute();
$update = $query->Update("users",["username"=>"test2"])->Where([["username","=","test","OR"],["id","=",1],["active"=>1]])->Execute(); //This will add an "OR" between the first and second where clauses and will add an "AND" between the second and third clauses automatically
```

### ZuluNiner\SECSQL->OrderBy()
#### params in order left to right

###### Required
* $columnsOrder

```php
$select = $query->Select("users")->OrderBy(['username','ASC'])->Execute();
$select = $query->Select("users")->OrderBy([['username','ASC'],['active'=>'DESC']])->Execute();
```

### ZuluNiner\SECSQL->Limit
#### params in order left to right
	
###### Required
* $number
	
###### Not Required: Defaults listed
* $startAfter = 0

```php
$select = $query->Select("users")->OrderBy(['username','ASC'])->Limit(1)->Execute();
$select = $query->Select("users")->OrderBy(['username','ASC'])->Limit(5)->Execute();
$select = $query->Select("users")->OrderBy(['username','ASC'])->Limit(5,1)->Execute(); //This will retrieve the second through sixth entry skipping the first
```


## PLANS FOR UPDATES

#### + Add One() and All() in replacement for Execute()
   This will allow the developer to specify if they want one entry or all of them without having to use limit every time
