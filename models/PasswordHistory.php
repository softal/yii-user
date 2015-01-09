<?php
namespace softal\user\models;
use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string  $password
 * @property integer $created_at
 *
 */
class PasswordHistory extends \yii\db\ActiveRecord
{
    /** @var \softal\user\Module */
    protected $module;

    /** @inheritdoc */
    public function init()
    {
        $this->module = \Yii::$app->getModule('user');
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_password_history}}';
    }

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->setAttribute('created_at', time());
        }
        return parent::beforeSave($insert);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }
}