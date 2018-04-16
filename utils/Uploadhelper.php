<?php
/**
 * Created by PhpStorm.
 * User: caimiao
 * Date: 17-8-3
 * Time: 上午9:19
 */

namespace app\utils;


use yii\base\Object;
use Yii;
use yii\helpers\ArrayHelper;

class Uploadhelper extends Object
{
    /**
     * @var string 上传后文件的名称
     */
    public $name;
    /**
     * @var string 上传文件的类型
     */
    public $type;
    /**
     * @var string 上传文件的临时文件名
     */
    public $tmp_name;
    /**
     * @var string 上传过程中的错误
     */
    public $error;
    /**
     * @var string 上传文件的大小
     */
    public $size;

    private $_rules = [];
    /**
     * 获取一个上传文件的实例
     * @param $name string 表单中文件的名称
     * @return Uploadhelper | NULL
     */
    public static function getFileinstanceByName($name)
    {
        if (isset($_FILES[$name]))
        {
            return Yii::createObject(ArrayHelper::merge(
                ['class' => Uploadhelper::className()],
                $_FILES[$name]
            ));
        }
        return NULL;
    }

    public function setLimits($rules)
    {

    }

    /**
     * 上传规则校验
     * @return bool
     */
    private function checkRules()
    {
        return TRUE;
    }

    /**
     * 执行上传操作
     * @param $desc 目的地址
     * @return string 访问url
     */
    public function uploadFile()
    {
        $fetch_desc_file = $this->getUploadFileDesc();
        if (is_string($fetch_desc_file) && (move_uploaded_file($this->tmp_name, $fetch_desc_file)))
        {
            $truncate_pos = strpos($fetch_desc_file, '/uploads');
            return substr($fetch_desc_file, $truncate_pos);
        }
        return null;
    }

    public function getUploadFileDesc($storage_root='')
    {
        $storage_root = Yii::$app->basePath;
        $storage_root = implode(DIRECTORY_SEPARATOR, [$storage_root, 'web', 'uploads', date('Y-m-d')]);
        if (!is_dir($storage_root))
        {
            $r = mkdir($storage_root, 0777, true);
            var_dump($r);
        }
        if (is_dir($storage_root))
            return $storage_root . DIRECTORY_SEPARATOR . time() . '_' . $this->name;

        return NULL;
    }
}