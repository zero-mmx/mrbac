<?php

namespace mrbac\models;

use mrbac\objs\AssignmentObj;
use Yii;

/**
 * This is the model class for table "yii_auth_item_child".
 *
 * @property string $parent
 * @property string $child
 *
 */
class Assignment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->authManager->assignmentTable;
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

    /**
     * @inheritdoc
     * @return \mrbac\model\search\AssignmeentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \mrbac\models\searchs\AssignmeentQuery(get_called_class());
    }

    /**
     * @property $role RoleObj
     * @inheritdoc
     */
    public static function assign($role, $userId)
    {
        $assignment = new AssignmentObj([
            'userId' => $userId,
            'roleName' => $role->name,
            'createdAt' => time(),
        ]);

        Yii::$app->authManager->db->createCommand()
            ->insert(Yii::$app->authManager->assignmentTable, [
                'user_id' => $assignment->userId,
                'item_name' => $assignment->roleName,
                'created_at' => $assignment->createdAt,
            ])->execute();

        return $assignment;
    }

    /**
     * @inheritdoc
     */
    public static function revoke($role, $userId)
    {
        if (empty($userId)) {
            return false;
        }

        return Yii::$app->authManager->db->createCommand()
            ->delete(Yii::$app->authManager->assignmentTable, ['user_id' => (string) $userId, 'item_name' => $role->name])
            ->execute() > 0;
    }
}
