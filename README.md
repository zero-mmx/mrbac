yii2 rbac manager
=================
like srbac for yii1. this is mrbac for yii2; rbac manager

该mrbac 是yii2的权限管理。类似Yii1 的srbac。使用简单。


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist zero-mmx/mrbac "*"
```

or add

```
"zero-mmx/mrbac": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
step 1:
import rbac sql table| 导入sql表,这边是mysql的，没有写migrate方式

/mrbac/sql/rbac.sql   table

step 2:
add components | 加入组件

components => [
    ...
      'authManager' => array(//mrbac 访问权限控制
         'class' => 'mrbac\MDbManager',
         'db' => 'db',  // The database component used
         'itemTable' => 'auth_item',// The itemTable name (default:auth_item)
         'assignmentTable' => 'auth_assignment', // The assignmentTable name (default:auth_assignment)
         'itemChildTable' => 'auth_item_child',// The itemChildTable name (default:auth_item_child)
         'ruleTable' => 'auth_rule'
     ),
    ...
]


step 3:

add mrbac to modules| 加入模块

modules =>[
    ...
    'mrbac' => [
        'class' => 'mrbac\MrbacModule',
        //'layout' => '@path/to/your/layout', // if you  want to change layout | 如果你想改变权限管理的界面ui的话,可以指定布局
        'idField' => 'id',        // id field of your User model that corresponds to Yii::$app->user->id
        'usernameField' => 'username', // username field of your User model | 用户名
        'userClassName' => 'path\models\User', |  指定的用户 ActiveRecord类 Model
        'searchClass' => 'path\models\UserSearch',    // fully qualified class name of your User model for searching
        'debug' => true,
        'allowedIPs'=>array('*'),
        'alwaysAllows' => [
            '/home/index',
            '/home/home',
            '/home/logout',
            '/home/login'
        ],
    ...
]

step 4:

add controller extends  RbacController| 把你想要进行权限管理的类进行继承 RbacController

class ...Controller extends RbacController{

}

step5: over

you can open :http:/**?r=mrbac/authitem/manager  or http:/**/mrbac/authitem/manager 到权限管理url

