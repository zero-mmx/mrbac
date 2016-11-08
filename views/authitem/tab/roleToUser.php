<?php
/**
 * roleToUser.php
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */

/**
 * The tab view for assigning roles to users
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.views.authitem.tabViews
 * @since 1.0.0
 */

use mrbac\MrbacAsset;
use yii\helpers\Json;
use yii\helpers\Url;
use \yii\widgets\ActiveForm;

$modules = Yii::$app->getModule('mrbac');
if ($modules->userClassName === null) {
//    $searchModel = new \mdm\admin\models\searchs\Assignment();
//    $dataProvider = $searchModel->search(\Yii::$app->request->getQueryParams(), $modules->userClassName, $modules->usernameField);
} else {
    $class = $modules->userClassName;
    $userClass = new $class;
}

/** @var \yii\data\ActiveDataProvider $dataProvider */
$userArr = array();
if(!empty($userClass)){
    $id = $modules->idField;
    $username= $modules->usernameField;
    $models = $userClass->find()->all();
    if(!empty($models)){
        foreach($models as $model){
            $userArr[] = [
                'uid'=>$model->$id,
                'username'=>$model->$username,
            ];
        }
    }
}

 ?>
<!-- USER -> ROLES -->
<div class="mrbac">
    <?php $form = ActiveForm::begin()?>
        <div class="row">
            <div class="col-lg-3">
                <label style="text-align: center"><?= "用户" ?>:</label>
                <input id="search-avaliable"><br>
                <select name="Item[uid]" id="list-user" multiple size="20" style="width: 100%">
                    <?php
                        if(!empty($userArr)){
                            foreach($userArr as $user){
                                echo \yii\helpers\Html::tag('option',$user['username'],array(
                                    'value'=>$user['uid']
                                ));
                            }
                        }
                    ?>
                </select>

            </div>
            <div  class="col-lg-9">
                <div class="col-lg-5">
                    <?= "已经分配角色" ?>:
                    <input id="search-assigned-user"><br>
                    <select name="AuthItem[name][revoke][]" id="list-assigned-user" multiple size="20" style="width: 100%">
                    </select>
                </div>
                <div class="col-lg-2">
                    <br><br>
                    <a href="#" id="btn-revoke-user" class="btn btn-success" style="margin: 5px;">&gt;&gt;</a><br>
                    <a href="#" id="btn-assign-user" class="btn btn-danger" style="margin: 5px;">&lt;&lt;</a>
                </div>
                <div class="col-lg-5">
                    <?= "尚未分配角色" ?>:
                    <input id="search-avaliable-user"><br>
                    <select name="AuthItem[name][assign][]" id="list-avaliable-user" multiple size="20" style="width: 100%">
                    </select>
                </div>
            </div>
        </div>
    <?php ActiveForm::end() ?>

  <br/>
</div>

<?php

$js = <<<JS
    jQuery('body').on('change','#list-user',function(){jQuery.ajax({'dataType':'json','type':'POST','url':'get-items?type=role','beforeSend':function(){
//        $("#loadMess").addClass("srbacLoading");
    },'complete':function(){
//        $("#loadMess").removeClass("srbacLoading");
    },'cache':false,'data':jQuery(this).parents("form").serialize(),'success':function(data){

    //jQuery("#roles").html(html);

        refreshUserForm(data);

    }});return false;});

    jQuery('#btn-assign-user').on('click', function () {
        jQuery.ajax({
         'dataType':'json',
          'type': 'POST',
            'beforeSend': function () {
                //$("#loadMess").addClass("srbacLoading");
            },
            'complete': function () {
                //$("#loadMess").removeClass("srbacLoading");
            },
            'url': 'edit-assign?type=assign-user',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function (data) {
                refreshUserForm(data);
            }
        });
        return false;
    });
    jQuery('#btn-revoke-user').on('click', function () {
        jQuery.ajax({
           'dataType':'json',
            'type': 'POST',
            'beforeSend': function () {
                //$("#loadMess").addClass("srbacLoading");
            },
            'complete': function () {
                //$("#loadMess").removeClass("srbacLoading");
            },
            'url': 'edit-assign?type=revoke-user',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function (data) {
                refreshUserForm(data);
            }
        });
        return false;
    });

    function refreshUserForm(data){
  //data = eval("("+data+")");
       $("#list-avaliable-user").html("");
       $("#list-assigned-user").html("");
        if(data.avaliable!=null && data.avaliable){
            for(key in data.avaliable){
                var value = data.avaliable[key];
                var op ="<option value="+key+">"+value+"</option>";
                $("#list-avaliable-user").append(op);
            }
        }

        if(data.assigned!=null && data.assigned){
            for(key in data.assigned){
                 var value = data.assigned[key];
                 var op ="<option value="+key+">"+value+"</option>";
                $("#list-assigned-user").append(op);
            }
        }
    }
JS;

$this->registerJs($js);

?>
