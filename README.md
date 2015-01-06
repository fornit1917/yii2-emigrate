yii2-emigrate
=========

Yii2 Framework extension for simplification of code generation when creating some migrations. Code for up- and down-method 
in migration class may be generated based on the name of migration or additional command line parameters.

### Install

Install via composer:

`composer require "fornit1917/yii2-emigrate:dev-master"`

Then add ExtendedMigrateController in your controllerMap for console applications (file config/console.php):
 
```
    'controllerMap' => [
        'emigrate' => 'fornit1917\yii2emigrate\ExtendedMigrateController'
    ],
```

Now you can create migration with new yii2-emigrate:

`./yii emigrate/create create_table_table_name`

### Usage

For automatic code generation migration it is necessary to give a special name:

* create_table_table_name - for creation table with name "table_name"
* drop_table_table_name - for dropping table with name "table_name"
* add_column_column_name_in_table_name - for adding new column with name "column_name" in table "table_name"
* drop_column_column_name_in_table_name - for dropping column with name "column_name" in table "table_name"

#### Create migrations for create table or drop table

`./yii emigrate/create drop_table_table_name`

This command generate the next code:

```
<?php

use yii\db\Schema;
use yii\db\Migration;

class m150105_202502_create_table_table_name extends Migration
{
    public function up()
    {
        $this->createTable('table_name', [], 'ENGINE=InnoDB CHARSET=utf8');
    }

    public function down()
    {
        $this->dropTable('table_name');
    }
}
```

If you do not need the option "ENGINE=InnoDB CHARSET=utf8", you can specify its value on the command line option *tableOptions*:

`./yii emigrate/create **create_table_**table_name --tableOptions="ENGINE=MyISAM"`

or empty value:

`./yii emigrate/create **create_table_**table_name --tableOptions=0`

The migration for drop table is generated similarly:

`./yii emigrate/create drop_table_table_name`

#### Create migrations for add column and drop column

`./yii emigrate/create add_column_column_name_in_table_name`

This command generate the next code:

```
<?php

use yii\db\Schema;
use yii\db\Migration;

class m150106_102312_add_column_column_name_in_table_name extends Migration
{
    public function up()
    {
        $this->addColumn('table_name', 'column_name', '');
    }

    public function down()
    {
        $this->dropColumn('table_name', 'column_name');
    }
}
```

Type of column is empty. This parameter can be specified in the command line option *columnType*:

`./yii emigrate/create add_column_column_name_in_table_name --columnType="int(11) not null default 0"`

This command generate the next code:

```
<?php

use yii\db\Schema;
use yii\db\Migration;

class m150106_102312_add_column_column_name_in_table_name extends Migration
{
    public function up()
    {
        $this->addColumn('table_name', 'column_name', 'int(11) not null default 0');
    }

    public function down()
    {
        $this->dropColumn('table_name', 'column_name');
    }
}
```

It is completely finished migration that does not require manual rework!

The migration for drop column is generated similarly:

`./yii emigrate/create drop_column_column_name_in_table_name --columnType="int(11) not null default 0"`

#### Command line options

The following command line options can be used:

* command
    * create-table
    * drop-table
    * add-column
    * drop-column
* table - name of table in migration
* tableOptions - options for command create-table (for create-table and drop-table)
* column - name of column in migration (for add-column and drop-column)
* columnType - type of column in migration (for add-column and drop-column)

With these options, you can generate the migration  not respecting the naming conventions. For example:

`./yii emigrate/create my_cool_migration --command=add-column --column=column_name --columnType=int`