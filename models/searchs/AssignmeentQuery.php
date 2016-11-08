<?php

namespace mrbac\model\search;

/**
 * This is the ActiveQuery class for [[\mrbac\models\Assignment]].
 *
 * @see \mrbac\models\Assignment
 */
class AssignmeentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \mrbac\models\Assignment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \mrbac\models\Assignment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}