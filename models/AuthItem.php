<?php

namespace mrbac\models;

use mrbac\objs\ItemObj;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "yii_auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property YiiAuthAssignment[] $yiiAuthAssignments
 * @property YiiAuthRule $ruleName
 * @property YiiAuthItemChild[] $yiiAuthItemChildren
 * @property YiiAuthItemChild[] $yiiAuthItemChildren0
 */
class AuthItem extends \yii\db\ActiveRecord
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;
    const TYPE_TASK = 3;

    public static $TYPES = array(1=>'Role',2=>'Permission',3=>'Task');


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->authManager->itemTable;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getYiiAuthAssignments()
    {
        return $this->hasMany(YiiAuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(YiiAuthRule::className(), ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getYiiAuthItemChildren()
    {
        return $this->hasMany(YiiAuthItemChild::className(), ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getYiiAuthItemChildren0()
    {
        return $this->hasMany(YiiAuthItemChild::className(), ['child' => 'name']);
    }

    /**
     * @property $role RoleObj
     * @inheritdoc
     */
    public static function assign($name)
    {
        $item = new ItemObj([
            'name' => $name,
            'type'=>ItemObj::TYPE_PERMISSION,
            'ruleName'=>null,
            'createdAt' => time(),
            'updatedAt' => time(),
        ]);

        Yii::$app->authManager->db->createCommand()
            ->insert(Yii::$app->authManager->itemTable, [
                'name' => $item->name,
                'type' => $item->type,
//                'rule_name' => $item->$ruleName,
                'created_at' => $item->createdAt,
                'updated_at' => $item->updatedAt,
            ])->execute();

        return $item;
    }

    /**
     * @inheritdoc
     */
    public static function revoke($name)
    {
        if (empty($name)) {
            return false;
        }

        return Yii::$app->authManager->db->createCommand()
            ->delete(Yii::$app->authManager->itemTable, ['name' => (string) $name, 'type' => ItemObj::TYPE_PERMISSION])
            ->execute() > 0;
    }
}
