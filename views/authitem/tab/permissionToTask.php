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
            <div class="col-lg-3">
                <label style="text-align: center"><?= "任务" ?>:</label>
                <input id="search-avaliable"><br>
                <select name="Item[task]" id="list-permission" multiple size="20" style="width: 100%">
                    <?php
                        if(!empty($itemArr)){
                            foreach($itemArr as $item){
                                echo \yii\helpers\Html::tag('option',$item['name'],array(
                                    'value'=>$item['id']
                                ));
                            }
                        }
                    ?>
                </select>

            </div>
            <div  class="col-lg-9">
                <div class="col-lg-5">
                    <?= "已经分配权限" ?>:
                    <input id="search-assigned-permission"><br>
                    <select name="AuthItem[name][revoke][]" id="list-assigned-permission" multiple size="20" style="width: 100%">
                    </select>
                </div>
                <div class="col-lg-2">
                    <br><br>
                    <a href="#" id="btn-revoke-permission" class="btn btn-success" style="margin: 5px;">&gt;&gt;</a><br>
                    <a href="#" id="btn-assign-permission" class="btn btn-danger" style="margin: 5px;">&lt;&lt;</a>
                </div>
                <div class="col-lg-5">
                    <?= "尚未分配权限" ?>:
                    <input id="search-avaliable-permission"><br>
                    <select name="AuthItem[name][assign][]" id="list-avaliable-permission" multiple size="20" style="width: 100%">
                    </select>
                </div>
            </div>
        </div>
    <?php ActiveForm::end() ?>

  <br/>
</div>

<?php

$js = <<<JS
    jQuery('body').on('change','#list-permission',function(){jQuery.ajax({'dataType':'json','type':'POST','url':'get-items?type=permission','beforeSend':function(){
//        $("#loadMess").addClass("srbacLoading");
    },'complete':function(){
//        $("#loadMess").removeClass("srbacLoading");
    },'cache':false,'data':jQuery(this).parents("form").serialize(),'success':function(data){

    //jQuery("#roles").html(html);

        refreshPerForm(data);

    }});return false;});

    jQuery('#btn-assign-permission').on('click', function () {
        jQuery.ajax({
         'dataType':'json',
          'type': 'POST',
            'beforeSend': function () {
                //$("#loadMess").addClass("srbacLoading");
            },
            'complete': function () {
                //$("#loadMess").removeClass("srbacLoading");
            },
            'url': 'edit-assign?type=assign-permission',
            'cache': false,
            'data': jQuery(this).parents("form").serialize(),
            'success': function (data) {
                refreshPerForm(data);
            }
        });
        return false;
    });
    jQuery('#btn-revoke-permission').on('click', function () {
        jQuery.ajax({
           'dataType':'json',
            'type': 'POST',
            'beforeSend': function () {
                //$("#loadMess").addClass("srbacLoading");
            },
            'complete': function () {
                //$("#loadMess").removeClass("srbacLoading");
            },
            'url': 'edit-assign?type=revoke-permission',
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
       $("#list-avaliable-permission").html("");
       $("#list-assigned-permission").html("");
        if(data.avaliable!=null && data.avaliable){
            for(key in data.avaliable){
                var value = data.avaliable[key];
                var op ="<option value="+key+">"+value+"</option>";
                $("#list-avaliable-permission").append(op);
            }

        }

        if(data.assigned!=null && data.assigned){
            for(key in data.assigned){
                 var value = data.assigned[key];
                 var op ="<option value="+key+">"+value+"</option>";
                $("#list-assigned-permission").append(op);
            }
        }
    }
JS;

$this->registerJs($js);


?>
