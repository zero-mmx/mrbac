<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace mrbac\objs;

use Yii;
use yii\base\Object;

/**
 * Assignment represents an assignment of a role to a user.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class ItemChildObj extends Object
{
    /**
     * @return string the role name
     */
    public $parent;
    /**
     * @var integer UNIX timestamp representing the assignment creation time
     */
    public $child;
}
