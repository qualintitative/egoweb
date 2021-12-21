<?php
namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\helpers\Tools;
use app\models\Study;

/**
 * User model
 *
 * @property integer $id
 * @property string $password_hash (not used)
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{

    // Roles
    const ROLE_MATCHER = 1;   // Can view specific studies
    const ROLE_INTERVIEWER = 3;   // Can view specific studies
    const ROLE_ADMIN       = 5;   // Can view and edit studies
    const ROLE_SUPERADMIN  = 11;  // Super user

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    private $_studies = [];

    public static function roles()
    {
        return [
            1 => "Matcher",
            3 => "Interviewer",
            5 => "Admin",
            11 => "Super Admin",
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($email)
    {
        $users = User::find()->all();
        foreach ($users as $u) {
            if ($u->email == trim($email)) {
                return $u;
            }
        }
        return null;
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function isAdmin()
    {
        return $this->permissions >= 5;
    }

    public function isSuperAdmin()
    {
        return $this->permissions == 11;
    }


    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $this->password, $matches)
            || $matches[1] < 4
            || $matches[1] > 30
        ) {
            return false;
        } else {
            return Yii::$app->security->validatePassword($password, $this->password);
        }
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function beforeSave($insert)
    {
        $this->name = Tools::encrypt($this->name);
        $this->email = Tools::encrypt($this->email);

        return parent::beforeSave($insert);
    }

    /**
     * Decrypts "name" and "email" attributes after they're found.
     */
    public function afterFind()
    {
        $this->name = Tools::decrypt($this->name);
        $this->email = Tools::decrypt($this->email);

        return parent::afterFind();
    }


    public function getStudies()
    {
        if (count($this->_studies) == 0) {
            if (!Yii::$app->user->identity->isSuperAdmin()) {
                $userId = Yii::$app->user->identity->id;
                $interviewers = Interviewer::findAll(["interviewerId"=>$userId]);
                $studyIds = array();
                foreach ($interviewers as $i) {
                    $studyIds[] = $i->studyId;
                }
                $this->_studies = Study::find()
                    ->where(["userId"=>$userId])
                    ->orWhere(["id"=>$studyIds])
                    ->orderBy(
                        [
                        'multiSessionEgoId' => SORT_DESC,
                        'id'=>SORT_DESC
                    ]
                    )->all();
            } else {
                $this->_studies = Study::find()->orderBy(
                    [
                    'multiSessionEgoId' => SORT_DESC,
                    'id'=>SORT_DESC
                ]
                )->all();
            }
        }
        return $this->_studies;
    }
}
