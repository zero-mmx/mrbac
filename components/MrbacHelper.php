<?php
/**
 * Created by PhpStorm.
 * User: chenqiang
 * Date: 9/29/2015
 * Time: 18:12
 */

namespace mrbac\components;


use mrbac\models\Assignment;
use mrbac\models\AuthItem;
use mrbac\objs\AssignmentObj;
use mrbac\objs\ItemChildObj;
use mrbac\objs\ItemObj;
use yii\caching\TagDependency;
use yii\db\Query;
use mrbac\objs\RoleObj;

use Yii;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;

class MrbacHelper {

    const CACHE_TAG_PERMISSION = 'mrbac.helper.permission';

    public static function getTypeItems($type){
        $data = array();
        $authModels = AuthItem::find()->andOnCondition('type='.$type)->all();
        if(!empty($authModels)){
            foreach($authModels as $model){
                $data[$model->name] = new ItemObj([
                    'name'=>$model->name,
                    'type'=>$type,
                ]);
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public static function getAssignments($userId)
    {
        if (empty($userId)) {
            return [];
        }

        $query = (new Query())
            ->from(\Yii::$app->authManager->assignmentTable)
            ->where(['user_id' => (string) $userId]);

        $assignments = [];
        foreach ($query->all(\Yii::$app->authManager->db) as $row) {
            $assignments[$row['item_name']] = new AssignmentObj( [
                'userId' => $row['user_id'],
                'roleName' => $row['item_name'],
                'createdAt' => $row['created_at'],
            ]);
        }

        return $assignments;
    }

    /**
     * @param $itemName ItemChildObj
     * @return array
     */
    public static function getItemChild($itemName){
        if (empty($itemName)) {
            return [];
        }

        $query = (new Query())
            ->from(\Yii::$app->authManager->itemChildTable)
            ->where(['parent' => (string) $itemName]);

        $assignments = [];
        foreach ($query->all(\Yii::$app->authManager->db) as $row) {
            $assignments[$row['child']] = new ItemChildObj( [
                'parent' => $row['parent'],
                'child' => $row['child'],
            ]);
        }

        return $assignments;
    }

    /**
     * Get list of application routes
     * @return array
     */
    public function getAppRoutes()
    {
        $key = __METHOD__;
        $cache = Configs::instance()->cache;
        //if ($cache === null || ($result = $cache->get($key)) === false) {
            $result = [];
        $this->getRouteRecrusive(Yii::$app, $result);
            if ($cache !== null) {
                $cache->set($key, $result, Configs::instance()->cacheDuration, new TagDependency([
                    'tags' => self::CACHE_TAG_PERMISSION
                ]));
            }
        //}

        if(!empty($result)){
            $tempArr = array();
            foreach($result as $route){
                $item =  new ItemObj();
                $item->name = $route;
                $tempArr[$route] = $item;
            }
            $result = $tempArr;
        }

        return $result;
    }

    /**
     * Get route(s) recrusive
     * @param \yii\base\Module $module
     * @param array $result
     */
    private function getRouteRecrusive($module, &$result)
    {
        $token = "Get Route of '" . get_class($module) . "' with id '" . $module->uniqueId . "'";
        Yii::beginProfile($token, __METHOD__);
        try {

            foreach ($module->getModules() as $id => $child) {
                if (($child = $module->getModule($id)) !== null) {
                    $this->getRouteRecrusive($child, $result);
                }
            }

            foreach ($module->controllerMap as $id => $type) {
                $this->getControllerActions($type, $id, $module, $result);
            }

            $namespace = trim($module->controllerNamespace, '\\') . '\\';
            $this->getControllerFiles($module, $namespace, '', $result);

            $result[] = ($module->uniqueId === '' ? '' : '/' . $module->uniqueId) . '/*';
        } catch (\Exception $exc) {
            print_r($exc->getTrace());exit;
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Get list controller under module
     * @param \yii\base\Module $module
     * @param string $namespace
     * @param string $prefix
     * @param mixed $result
     * @return mixed
     */
    private function getControllerFiles($module, $namespace, $prefix, &$result)
    {
//        $path = @Yii::getAlias('@' . str_replace('\\',DIRECTORY_SEPARATOR, $namespace));
        $path = @Yii::getAlias('@' . str_replace('\\', '/', $namespace));
        $token = "Get controllers from '$path'";
        Yii::beginProfile($token, __METHOD__);
        try {
            if (!is_dir($path)) {
                return;
            }
            foreach (scandir($path) as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                    $this->getControllerFiles($module, $namespace . $file . DIRECTORY_SEPARATOR, $prefix . $file . DIRECTORY_SEPARATOR, $result);
                } elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
                    $id = Inflector::camel2id(substr(basename($file), 0, -14));
                    $className = $namespace . Inflector::id2camel($id) . 'Controller';
                    if (strpos($className, '-') === false && class_exists($className) && is_subclass_of($className, 'mrbac\base\RbacController')) {
                        $this->getControllerActions($className, $prefix . $id, $module, $result);
                    }
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Get list action of controller
     * @param mixed $type
     * @param string $id
     * @param \yii\base\Module $module
     * @param string $result
     */
    private function getControllerActions($type, $id, $module, &$result)
    {
        $token = "Create controller with cofig=" . VarDumper::dumpAsString($type) . " and id='$id'";
        Yii::beginProfile($token, __METHOD__);
        try {
            /* @var $controller \mrbac\base\RbacController */
            $controller = Yii::createObject($type, [$id, $module]);
            $result[] = '/' . $controller->uniqueId . '/*';
            $this->getActionRoutes($controller, $result);
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Get route of action
     * @param \yii\base\Controller $controller
     * @param array $result all controller action.
     */
    private function getActionRoutes($controller, &$result)
    {
        $token = "Get actions of controller '" . $controller->uniqueId . "'";
        Yii::beginProfile($token, __METHOD__);
        try {
            $prefix = '/' . $controller->uniqueId . '/';
            foreach ($controller->actions() as $id => $value) {
                $result[] = $prefix . $id;
            }
            $class = new \ReflectionClass($controller);
            foreach ($class->getMethods() as $method) {
                $name = $method->getName();
                if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
                    $result[] = $prefix . Inflector::camel2id(substr($name, 6));
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Ivalidate cache
     */
    protected function invalidate()
    {
        if (Configs::instance()->cache !== null) {
            TagDependency::invalidate(Configs::instance()->cache, self::CACHE_TAG_PERMISSION);
        }
    }
}