<?php
/**
 * Created by PhpStorm.
 * User: chenqiang
 * Date: 9/24/2015
 * Time: 22:12
 */

namespace mrbac\base;


use yii\web\Controller;

use Yii;

class RbacController extends Controller{

    private $_access = array();

    public function beforeAction($action){

        $prefix = '/' . $this->uniqueId . '/';
        $access = $prefix.$action->id;

        $this->allowedIps();

        //Always allow access if $access is in the allowedAccess array
        if (in_array($access, $this->allowedAccess())) {
            return true;
        }


        //Allow access when srbac is in debug mode
        if (Yii::$app->getModule('mrbac')->debug) {
            return true;
        }

        // Check for srbac access
        if (!Yii::$app->user->can($access) || Yii::$app->user->isGuest) {
//        if (!$this->checkAccess($access,Yii::$app->user->getId()) || Yii::app()->user->isGuest) {
            $this->onUnauthorizedAccess();
        } else {
            return true;
        }

        return parent::beforeAction($action);
    }

    protected function checkAccess($permissionName,$userId,$params = [], $allowCaching = true){
        if ($allowCaching && empty($params) && isset($this->_access[$permissionName])) {
            return $this->_access[$permissionName];
        }

        $access = $this->getAuthManager()->checkAccess($userId, $permissionName, $params);
        if ($allowCaching && empty($params)) {
            $this->_access[$permissionName] = $access;
        }

        return $access;
    }

    /**
     * The auth items that access is always  allowed. Configured in srbac module's
     * configuration
     * @return The always allowed auth items
     */
    protected function allowedAccess() {
        return Yii::$app->getModule('mrbac')->getAlwaysAllowed();
    }

    /**
     * The auth items that access is always  allowed. Configured in srbac module's
     * configuration
     */
    protected function allowedIps() {
        return Yii::$app->getModule('mrbac')->allowIps();
    }

    protected function onUnauthorizedAccess(){
        throw new \yii\web\ForbiddenHttpException('对不起，您现在还没获此操作的权限');
    }
}