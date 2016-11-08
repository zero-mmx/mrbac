<?php
/**
 * Created by PhpStorm.
 * User: chenqiang
 * Date: 9/24/2015
 * Time: 22:11
 */

namespace mrbac\controllers;


use mrbac\components\MrbacHelper;
use mrbac\base\RbacController;
use mrbac\models\Assignment;
use mrbac\models\AuthItem;
use mrbac\models\ItemChild;
use mrbac\models\searchs\AuthItemSearch;

use mrbac\objs\ItemObj;
use mrbac\objs\RoleObj;
use Yii;
use yii\rbac\Role;

class AuthitemController extends RbacController{

    public $defaultAction ='manager';
    public $enableCsrfValidation = false;

//    public $layout = 'main';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

    }

    /**
     * 管理授权项
     */
    public function actionManager(){

        $searchModel = new AuthItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('manager',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAssign(){
        return $this->render('assign');
    }

    /**
     * 自动建立授权项
     */
    public function actionAuto(){

        return $this->render('auto');
    }

    /**
     * 编辑总是允许
     */
    public function actionEditAllowed(){

    }

    /**
     * 清除授权项
     */
    public function actionClearObsolete(){

    }

    public function actionSearchAssign(){

    }

    public function actionEditAssign(){
        $type = isset($_REQUEST['type'])?$_REQUEST['type']:"";

        /**@var \yii\rbac\ManagerInterface  $authManager **/
        $authManager = Yii::$app->authManager;

        $data = array();
        $key = '';

        if($type =='revoke-user'){
            $revokeItems = isset($_REQUEST['AuthItem']['name']['revoke'])?$_REQUEST['AuthItem']['name']['revoke']:array();
            $uid = isset($_REQUEST['Item']['uid'])?$_REQUEST['Item']['uid']:0;
            if(!empty($revokeItems)){
                foreach($revokeItems as $item){
                    $role = new RoleObj();
                    $role->name = $item;

                    Assignment::revoke($role,$uid);
                }
            }
            $type = ItemObj::TYPE_ROLE;
            $key = $uid;
        }else if($type =='assign-user'){
            $assignItems = isset($_REQUEST['AuthItem']['name']['assign'])?$_REQUEST['AuthItem']['name']['assign']:array();
            $uid = isset($_REQUEST['Item']['uid'])?$_REQUEST['Item']['uid']:0;
            if(!empty($assignItems)){
                foreach($assignItems as $item){
                    $role = new RoleObj();
                    $role->name = $item;
                    Assignment::assign($role,$uid);
                }
            }
            $type = ItemObj::TYPE_ROLE;
            $key = $uid;
        }else if($type =='revoke-role'){
            $assignItems = isset($_REQUEST['AuthItem']['name']['revoke'])?$_REQUEST['AuthItem']['name']['revoke']:array();
            $key = isset($_REQUEST['Item']['task'])?$_REQUEST['Item']['task']:"";

            if(!empty($assignItems)){
                foreach($assignItems as $item){
                    ItemChild::revoke($key,$item);
                }
            }
            $type = ItemObj::TYPE_TASK;

        }else if($type =='assign-role'){
            $assignItems = isset($_REQUEST['AuthItem']['name']['assign'])?$_REQUEST['AuthItem']['name']['assign']:array();
            $key = isset($_REQUEST['Item']['task'])?$_REQUEST['Item']['task']:"";

            if(!empty($assignItems)){
                foreach($assignItems as $item){
                    ItemChild::assign($key,$item);
                }
            }

            $type = ItemObj::TYPE_TASK;
        }else if($type =='revoke-permission'){
            $assignItems = isset($_REQUEST['AuthItem']['name']['revoke'])?$_REQUEST['AuthItem']['name']['revoke']:array();
            $key = isset($_REQUEST['Item']['task'])?$_REQUEST['Item']['task']:"";

            if(!empty($assignItems)){
                foreach($assignItems as $item){
                    ItemChild::revoke($key,$item);
                }
            }
            $type = ItemObj::TYPE_PERMISSION;

        }else if($type =='assign-permission'){
            $assignItems = isset($_REQUEST['AuthItem']['name']['assign'])?$_REQUEST['AuthItem']['name']['assign']:array();
            $key = isset($_REQUEST['Item']['task'])?$_REQUEST['Item']['task']:"";

            if(!empty($assignItems)){
                foreach($assignItems as $item){
                    ItemChild::assign($key,$item);
                }
            }
            $type = ItemObj::TYPE_PERMISSION;
        }else if($type =='assign-route'){
            $assignItems = isset($_REQUEST['AuthItem']['name']['assign'])?$_REQUEST['AuthItem']['name']['assign']:array();

            if(!empty($assignItems)){
                foreach($assignItems as $item){
                    AuthItem::assign($item);
                }
            }
        }else if($type =='revoke-route'){
            $revokeItems = isset($_REQUEST['AuthItem']['name']['revoke'])?$_REQUEST['AuthItem']['name']['revoke']:array();

            if(!empty($revokeItems)){
                foreach($revokeItems as $item){
                    AuthItem::revoke($item);
                }
            }
        }
        if($type=='assign-route' || $type=='revoke-route'){
            $data = $this->getRouteItems();
        }else{
            $data = $this->getAuthItems($key,$type);
        }

        return json_encode($data);
    }


    public function actionGetItems(){
        $type = isset($_REQUEST['type'])?$_REQUEST['type']:"";

        $key = '';
        if($type == "role"){
            $key = isset($_REQUEST['Item']['uid'])?$_REQUEST['Item']['uid']:0;
            $type = AuthItem::TYPE_ROLE;
        }else if($type == 'task'){
            $key = isset($_REQUEST['Item']['task'])?$_REQUEST['Item']['task']:"";
            $type = AuthItem::TYPE_TASK;
        }else if($type == 'permission'){
            $key = isset($_REQUEST['Item']['task'])?$_REQUEST['Item']['task']:"";
            $type = AuthItem::TYPE_PERMISSION;
        }

        return json_encode($this->getAuthItems($key,$type));
    }

    /**
     * @param $key  user role task
     * @param $type role task permission
     * @param string $term
     * @return array
     */
    public function getAuthItems($key,$type,$term=''){
        $avaliable = [];
        $assigned = [];

        if($type == ItemObj::TYPE_ROLE){
            $roles = MrbacHelper::getTypeItems(ItemObj::TYPE_ROLE);
            foreach (MrbacHelper::getAssignments($key) as $assigment) {
                if (isset($roles[$assigment->roleName])) {
                    if (empty($term) || strpos($assigment->roleName, $term) !== false) {
                        $assigned[$assigment->roleName] = $assigment->roleName;
                    }
                    unset($roles[$assigment->roleName]);
                }
            }

            if (count($roles)) {
                foreach ($roles as $role) {
                    if (empty($term) || strpos($role->name, $term) !== false) {
                        $avaliable[$role->name] = $role->name;
                    }
                }
            }
        }else if($type == ItemObj::TYPE_TASK){
            $items = MrbacHelper::getTypeItems(ItemObj::TYPE_TASK);

            foreach(MrbacHelper::getItemChild($key) as $itemChild){
                if(isset($items[$itemChild->child])){
                    $assigned[$itemChild->child] = $itemChild->child;
                    unset($items[$itemChild->child]);
                }
            }

            if (count($items)) {
                foreach ($items as $role) {
                    if (empty($term) || strpos($role->name, $term) !== false) {
                        $avaliable[$role->name] = $role->name;
                    }
                }
            }
        }else if($type == ItemObj::TYPE_PERMISSION){
            $perArr = MrbacHelper::getTypeItems(ItemObj::TYPE_PERMISSION);

            $taskForPer = MrbacHelper::getItemChild($key);
            foreach($taskForPer as $itemChild){
                if(array_key_exists($itemChild->child,$perArr)){
                    $assigned[$itemChild->child] = $itemChild->child;
                    unset($perArr[$itemChild->child]);
                }
            }

            if (count($perArr)) {
                foreach ($perArr as $role) {
                    if (empty($term) || strpos($role->name, $term) !== false) {
                        $avaliable[$role->name] = $role->name;
                    }
                }
            }
        }

        return ['avaliable'=>$avaliable,'assigned'=>$assigned];
    }

    public function actionRouteItems(){
        return json_encode($this->getRouteItems());
    }

    public function getRouteItems(){
        $avaliable = [];
        $assigned = [];

        $mrHelper = new MrbacHelper();
        $perArr = $mrHelper->getAppRoutes();

        $existItems = MrbacHelper::getTypeItems(ItemObj::TYPE_PERMISSION);

        foreach($existItems as $item){
            if(array_key_exists($item->name,$perArr)){
                $assigned[$item->name] = $item->name;
                unset($perArr[$item->name]);
            }
        }

        if (count($perArr)) {
            foreach ($perArr as $role) {
                if (empty($term) || strpos($role->name, $term) !== false) {
                    $avaliable[$role->name] = $role->name;
                }
            }
        }

        $result = ['avaliable'=>$avaliable,'assigned'=>$assigned];
        return $result;
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AuthItem();


        if ($model->load(Yii::$app->request->post())) {
            if(empty($model->rule_name)){
                $model->rule_name = null;
            }

            if($model->save()){
                return $this->redirect(['manager', 'id' => $model->name]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->name]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing AuthItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['manager']);
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuthItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}