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
use mrbac\objs\ItemObj;
use mrbac\components\MrbacHelper;

$modules = Yii::$app->getModule('mrbac');
if ($modules->userClassName === null) {
//    $searchModel = new \mdm\admin\models\searchs\Assignment();
//    $dataProvider = $searchModel->search(\Yii::$app->request->getQueryParams(), $modules->userClassName, $modules->usernameField);
} else {
    $class = $modules->userClassName;
    $userClass = new $class;
}

/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var \yii\data\ActiveDataProvider $dataProvider */
$itemArr = array();
$dataArr = MrbacHelper::getTypeItems(ItemObj::TYPE_TASK);

if(!empty($dataArr)){
    /** @var mrbac\objs\ItemObj $item */
    foreach($dataArr as $item){
        $itemArr[] = [
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
        <div class="col-lg-5">
            <?= "已入库规则" ?>:
            <input id="search-assigned-permission"><br>
            <select name="AuthItem[name][revoke][]" id="list-assigned-route" multiple size="20" style="width: 100%">
            </select>
        </div>
        <div class="col-lg-2">
            <br><br>
            <a href="#" id="btn-revoke-route" class="btn btn-success" style="margin: 5px;">&gt;&gt;</a><br>
            <a href="#" id="btn-assign-route" class="btn btn-danger" style="margin: 5px;">&lt;&lt;</a>
        </div>
        <div class="col-lg-5">
            <?= "未入库路由" ?>:
            <input id="search-avaliable-permission"><br>
            <select name="AuthItem[name][assign][]" id="list-avaliable-route" multiple size="20" style="width: 100%">
            </select>
        </div>
    </div>
    <?php ActiveForm::end() ?>

    <br/>
</div>

<?php

$js = <<<JS
    $(function(){
        jQuery.ajax({'dataType':'json','type':'POST','url':'route-items','beforeSend':function(){
        },'complete':function(){
        },'cache':false,'data':jQuery(this).parents("form").serialize(),'success':function(data){
                refreshPerForm(data);
        } });
    });

    jQuery('#btn-assign-route').on('click', function () {
        jQuery.ajax({
         'dataType':'json',
          'type': 'POST',
            'beforeSend': function () {
                //$("#loadMess").addClass("srbacLoading");
            },
            'complete': function () {
                //$("#loadMess").removeClass("srbacLoading");
            },
            'url': 'edit-assign?type=assign-route',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function (data) {
                refreshPerForm(data);
            }
        });
        return false;
    });
    jQuery('#btn-revoke-route').on('click', function () {
        jQuery.ajax({
           'dataType':'json',
            'type': 'POST',
            'beforeSend': function () {
                //$("#loadMess").addClass("srbacLoading");
            },
            'complete': function () {
                //$("#loadMess").removeClass("srbacLoading");
            },
            'url': 'edit-assign?type=revoke-route',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function (data) {
                refreshPerForm(data);
            }
        });
        return false;
    });

    function refreshPerForm(data){
        //data = eval("("+data+")");
       $("#list-avaliable-route").html("");
       $("#list-assigned-route").html("");
        if(data.avaliable!=null && data.avaliable){
            for(key in data.avaliable){
                var value = data.avaliable[key];
                var op ="<option value="+key+">"+value+"</option>";
                $("#list-avaliable-route").append(op);
            }

        }

        if(data.assigned!=null && data.assigned){
            for(key in data.assigned){
                 var value = data.assigned[key];
                 var op ="<option value="+key+">"+value+"</option>";
                $("#list-assigned-route").append(op);
            }
        }
    }
JS;

$this->registerJs($js);


?>
