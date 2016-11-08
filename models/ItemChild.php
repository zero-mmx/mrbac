<?php

namespace mrbac\models;

use Yii;

/**
 * This is the model class for table "yii_auth_item_child".
 *
 * @property string $parent
 * @property string $child
 *
 * @property YiiAuthItem $parent0
 * @property YiiAuthItem $child0
 */
class ItemChild extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->authManager->itemChildTable;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent', 'child'], 'required'],
            [['parent', 'child'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parent' => 'Parent',
            'child' => 'Child',
        ];
    }

    public static function assign($parent,$child){
        $model = new ItemChild();
        $model->parent = $parent;
        $model->child = $child;
        $model->save();
    }

    public static function revoke($parent,$child){
        $item = ItemChild::find()->where("parent='{$parent}' and child='{$child}'")->one();
        !empty($item) && $item->delete();
    }
}
