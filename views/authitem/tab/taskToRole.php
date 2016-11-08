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

use \yii\widgets\ActiveForm;
use mrbac\models\AuthItem;
use mrbac\components\MrbacHelper;
use mrbac\objs\ItemObj;

$modules = Yii::$app->getModule('mrbac');
if ($modules->userClassName === null) {
//    $searchModel = new \mdm\admin\models\searchs\Assignment();
//    $dataProvider = $searchModel->search(\Yii::$app->request->getQueryParams(), $modules->userClassName, $modules->usernameField);
} else {
    $class = $modules->userClassName;
    $userClass = new $class;
}

/** @var \yii\data\ActiveDataProvider $dataProvider */
$roleArr = array();
$dataArr = MrbacHelper::getTypeItems(ItemObj::TYPE_ROLE);

if(!empty($dataArr)){
    /** @var mrbac\objs\ItemObj $item */
    foreach($dataArr as $item){
        $roleArr[] = [
            'id'=>$item->name,
            'name'=>$item->name,
        ];
    }
}

 ?>
<!-- USER -> ROLES -->
<div class="mrbac">
    <?php $form = ActiveForm::begin()?>
        <div class="row">
            <div class="col-lg-3">
                <label style="text-align: center"><?= "角色" ?>:</label>
                <input id="search-avaliable"><br>
                <select name="Item[task]" id="list-task" multiple size="20" style="width: 100%">
                    <?php
                        if(!empty($roleArr)){
                            foreach($roleArr as $user){
                                echo \yii\helpers\Html::tag('option',$user['name'],array(
                                    'value'=>$user['id']
                                ));
                            }
                        }
                    ?>
                </select>

            </div>
            <div  class="col-lg-9">
                <div class="col-lg-5">
                    <?= "已分配任务" ?>:
                    <input id="search-assigned-role"><br>
                    <select name="AuthItem[name][revoke][]" id="list-assigned-role" multiple size="20" style="width: 100%">
                    </select>
                </div>
                <div class="col-lg-2">
                    <br><br>
                    <a href="#" id="btn-revoke-role" class="btn btn-success" style="margin: 5px;">&gt;&gt;</a><br>
                    <a href="#" id="btn-assign-role" class="btn btn-danger" style="margin: 5px;">&lt;&lt;</a>
                </div>
                <div class="col-lg-5">
                    <?= "尚未分配任务" ?>:
                    <input id="search-avaliable-role"><br>
                    <select name="AuthItem[name][assign][]" id="list-avaliable-role" multiple size="20" style="width: 100%">
                    </select>
                </div>
            </div>
        </div>
    <?php ActiveForm::end() ?>

  <br/>
</div>

<?php

$js = <<<JS
    jQuery('body').on('change','#list-task',function(){jQuery.ajax({'dataType':'json','type':'POST','url':'get-items?type=task','beforeSend':function(){
//        $("#loadMess").addClass("srbacLoading");
    },'complete':function(){
//        $("#loadMess").removeClass("srbacLoading");
    },'cache':false,'data':jQuery(this).parents("form").serialize(),'success':function(data){

    //jQuery("#roles").html(html);

        refreshTaskForm(data);

    }});return false;});

    jQuery('#btn-assign-role').on('click', function () {
        jQuery.ajax({
         'dataType':'json',
          'type': 'POST',
            'beforeSend': function () {
                //$("#loadMess").addClass("srbacLoading");
            },
            'complete': function () {
                //$("#loadMess").removeClass("srbacLoading");
            },
            'url': 'edit-assign?type=assign-role',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function (data) {
                refreshTaskForm(data);
            }
        });
        return false;
    });
    jQuery('#btn-revoke-role').on('click', function () {
        jQuery.ajax({
           'dataType':'json',
            'type': 'POST',
            'beforeSend': function () {
                //$("#loadMess").addClass("srbacLoading");
            },
            'complete': function () {
                //$("#loadMess").removeClass("srbacLoading");
            },
            'url': 'edit-assign?type=revoke-role',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function (data) {
                refreshTaskForm(data);
            }
        });
        return false;
    });

    function refreshTaskForm(data){
        //data = eval("("+data+")");
       $("#list-avaliable-role").html("");
       $("#list-assigned-role").html("");
        if(data.avaliable!=null && data.avaliable){
            for(key in data.avaliable){
                var value = data.avaliable[key];
                var op ="<option value="+key+">"+value+"</option>";
                $("#list-avaliable-role").append(op);
            }

        }

        if(data.assigned!=null && data.assigned){
            for(key in data.assigned){
                 var value = data.assigned[key];
                 var op ="<option value="+key+">"+value+"</option>";
                $("#list-assigned-role").append(op);
            }
        }
    }
JS;

$this->registerJs($js);

?>
