<?php
/**
 * Created by PhpStorm.
 * User: chenqiang
 * Date: 9/29/2015
 * Time: 20:47
 */

namespace mrbac;

use mrbac\objs\AssignmentObj;
use mrbac\objs\ItemObj;
use yii\db\Query;
use yii\rbac\DbManager;

use Yii;
use yii\rbac\Rule;

class MDbManager extends DbManager{

    public function init()
    {
        parent::init();
    }

    /**
     * Performs access check for the specified user.
     * @param string the name of the operation that need access check
     * @param mixed the user ID. This should can be either an integer and a string representing
     * the unique identifier of a user. See {@link IWebUser::getId}.
     * @param array name-value pairs that would be passed to biz rules associated
     * with the tasks and roles assigned to the user.
     * @return boolean whether the operations can be performed by the user.
     */
    public function checkAccess($userId, $permissionName, $params = []) {
        if (!empty($this->defaultRoles) && in_array($permissionName,$this->defaultRoles)) {
            return true;
        }
        $sql = "SELECT name, type, description, t1.rule_name, t1.data FROM {$this->itemTable} t1, {$this->assignmentTable} t2 WHERE name=item_name AND user_id=:userid";
        $command = $this->db->createCommand($sql);
        $command->bindValue(':userid', $userId);

        // check directly assigned items
        $names = array();
        foreach ($command->queryAll() as $row) {
            Yii::trace('Checking permission "' . $row['name'] . '"', 'system.web.auth.CDbAuthManager');
            if ($this->executeRule($row['rule_name'], $params, unserialize($row['data']))) {
                if (strtolower($row['name']) === strtolower($permissionName)) {
                    return true;
                }
                $names[] = $row['name'];
            }
        }

        // check all descendant items
        while ($names !== array()) {
            $items = $this->getChildren($names);
//            if($names[0] =='权限分配'){
//                print_r($items);exit;
//            }
            $names = array();
            /** @var ItemObj $item */
            foreach ($items as $item) {
                if ($this->executeRule($userId,$item, $params)) {
                    if (strtolower($item->name) === strtolower($permissionName)) {
                        return true;
                    }
                    $names[] = $item->name;

                }
            }
        }

        return false;
    }

    /**
     * @param int|string $user
     * @param \mrbac\objs\ItemObj $item
     * @param array $params
     * @return bool
     * @throws InvalidConfigException
     */
    public function executeRule($user, $item, $params)
    {
        if (empty($item) || $item === null || $item->ruleName==null) {
            return true;
        }

        $rule = $this->getRule($item->ruleName);
        if ($rule instanceof Rule) {
            return $rule->execute($user, $item, $params);
        } else {
            throw new InvalidConfigException("Rule not found: {$item->ruleName}");
        }
    }

    /**
     * Populates an auth item with the data fetched from database
     * @param array $row the data from the auth item table
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row)
    {
//        $class = 'ItemObj';//$row['type'] == Item::TYPE_PERMISSION ? Permission::className() : Role::className();

        if (!isset($row['data']) || ($data = @unserialize($row['data'])) === false) {
            $data = null;
        }

        return new ItemObj([
            'name' => $row['name'],
            'type' => $row['type'],
            'description' => $row['description'],
            'ruleName' => $row['rule_name'],
            'data' => $data,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ]);
    }

//    /**
//     * @inheritdoc
//     */
//    public function checkAccess($userId, $permissionName, $params = [])
//    {
//        $assignments = $this->getAssignments($userId);
//        $this->loadFromCache();
//
//        if ($this->items !== null) {
//            return $this->checkAccessFromCache($userId, $permissionName, $params, $assignments);
//        } else {
//            return $this->checkAccessRecursive($userId, $permissionName, $params, $assignments);
//        }
//    }

    /**
     * Performs access check for the specified user based on the data loaded from cache.
     * This method is internally called by [[checkAccess()]] when [[cache]] is enabled.
     * @param string|integer $user the user ID. This should can be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $itemName the name of the operation that need access check
     * @param array $params name-value pairs that would be passed to rules associated
     * with the tasks and roles assigned to the user. A param with name 'user' is added to this array,
     * which holds the value of `$userId`.
     * @param Assignment[] $assignments the assignments to the specified user
     * @return boolean whether the operations can be performed by the user.
     * @since 2.0.3
     */
    protected function checkAccessFromCache($user, $itemName, $params, $assignments)
    {
        if (!isset($this->items[$itemName])) {
            return false;
        }

        $item = $this->items[$itemName];

        Yii::trace($item instanceof Role ? "Checking role: $itemName" : "Checking permission: $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        if (!empty($this->parents[$itemName])) {
            foreach ($this->parents[$itemName] as $parent) {
                if ($this->checkAccessFromCache($user, $parent, $params, $assignments)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Performs access check for the specified user.
     * This method is internally called by [[checkAccess()]].
     * @param string|integer $user the user ID. This should can be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $itemName the name of the operation that need access check
     * @param array $params name-value pairs that would be passed to rules associated
     * with the tasks and roles assigned to the user. A param with name 'user' is added to this array,
     * which holds the value of `$userId`.
     * @param Assignment[] $assignments the assignments to the specified user
     * @return boolean whether the operations can be performed by the user.
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments)
    {
        if (($item = $this->getItem($itemName)) === null) {
            return false;
        }

        Yii::trace($item instanceof Role ? "Checking role: $itemName" : "Checking permission: $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        $query = new Query;
        $parents = $query->select(['parent'])
            ->from($this->itemChildTable)
            ->where(['child' => $itemName])
            ->column($this->db);
        foreach ($parents as $parent) {
            if ($this->checkAccessRecursive($user, $parent, $params, $assignments)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        if (empty($userId)) {
            return [];
        }

        $sql = "SELECT name, type, description, t1.rule, t1.data, t2.rule AS rule2, t2.data AS data2 FROM {$this->itemTable} t1, {$this->assignmentTable} t2 WHERE name=itemname AND userid=:userid";
        $query = new Query();

        $query->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId])
            ->select
        ;

        $assignments = [];
        foreach ($query->all($this->db) as $row) {
            $assignments[$row['item_name']] = new AssignmentObj([
                'userId' => $row['user_id'],
                'roleName' => $row['item_name'],
                'createdAt' => $row['created_at'],
            ]);
        }

        return $assignments;
    }
}