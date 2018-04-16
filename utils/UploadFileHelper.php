<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/2/16
 * Time: 22:20
 */

namespace app\utils;


use app\models\AppGovAttachement;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class UploadFileHelper
{
    /**
     * 文件类型是否是图片或者其他
     */
    const TYPE_ADHERE = 0;
    const TYPE_IMAGE = 1;
    const TYPE_VOICE = 2;

    /**
     * @var UploadedFile
     */
    private $upload_file_instance;
    /**
     * @var
     */
    private $file_type;
    private $size = 4 * 1024 * 1024;
    private $errors = [];
    private $ext_name_adhere = ['application/msword'];
    private $ext_name_image = ['gif', 'png', 'jpeg', 'bmp', 'jpg'];

    public function getErrors() {
        return $this->errors;
    }

    /**
     * @param $filename
     * @return UploadFileHelper|null
     */
    public static function makeUploadInstance($filename) {
        $instance = UploadedFile::getInstanceByName($filename);
        if ($instance) {
            return new UploadFileHelper($instance);
        } else {
            return null;
        }
    }

    private function __construct($file_instance)
    {
        $this->upload_file_instance = $file_instance;
        if (in_array($this->upload_file_instance->type, $this->ext_name_image)) {
            $this->file_type = self::TYPE_IMAGE;
        } else {
            $this->file_type = self::TYPE_ADHERE;
        }

    }

    public function getBaseName() {
        return $this->upload_file_instance->getBaseName();
    }

    public function getExtName() {
        return $this->upload_file_instance->getExtension();
    }

    /**
     * 构造本地存放上传文件的路径
     * @return bool|string
     */
    private function makeLocalSavePath() {
        $today = date('Ym');
        $local_type = 'adhere';
        switch ($this->file_type) {
            case self::TYPE_IMAGE:
                $local_type = 'image';
                break;
            case self::TYPE_VOICE:
                $local_type = 'voice';
                break;
            default:
                break;
        }

        $stack_dir_path_absolute = implode(DIRECTORY_SEPARATOR, [\Yii::$app->basePath, 'web', 'uploadfiles', $local_type, $today]);
        $stack_dir_path_rel = implode(DIRECTORY_SEPARATOR, ['uploadfiles', $local_type, $today]);
        $pure_path_name = $this->parseUploadFilename();
        $ret_path_info = [
            'abs' => $stack_dir_path_absolute . DIRECTORY_SEPARATOR . $pure_path_name,
            'rel' => $stack_dir_path_rel . DIRECTORY_SEPARATOR . $pure_path_name
        ];
        if (!is_dir($stack_dir_path_absolute)) {
            if (mkdir($stack_dir_path_absolute, 0777, true)) {
                return $ret_path_info;
            } else {
                $this->errors['gen'] = '构造存放路径失败';
                return false;
            }
        } else {
            return $ret_path_info;
        }
        return false;
    }

    /**
     * 解析上传文件的原始名称，并作唯一化处理
     * 加上上传时间和上传人编号
     * @param $filename
     * @return string
     */
    private function parseUploadFilename() {
        $raw_fname = $this->upload_file_instance->getBaseName();
        $filename = pathinfo($raw_fname, PATHINFO_FILENAME);
        return implode('_', [$filename, time(), \Yii::$app->user->isGuest ? '0' : '' . \Yii::$app->user->getId(),
            '.' . $this->upload_file_instance->getExtension()]);
    }

    /**
     * 保存本地文件，并返回相对路径
     * @param bool $ret_rel_path
     * @return bool
     */
    private function saveLocal($ret_rel_path=true) {
        $save_path = $this->makeLocalSavePath();
        if (false !== $save_path) {
            if ($this->validate() && $this->upload_file_instance->saveAs($save_path['abs']))
                return $ret_rel_path ? $save_path['rel'] : $save_path['abs'];
        } else
            $this->errors['gen'] = 'make local save path failure';
        return false;
    }

    public function validate() {
        $ret_check_validate = true;
        if ($this->upload_file_instance->size > $this->size) {
            $ret_check_validate = false;
            $this->errors['size'] = '文件大小超过4M，当前大小 ' . $this->size;
        }
        if (!in_array($this->upload_file_instance->getExtension(), ArrayHelper::merge($this->ext_name_image, $this->ext_name_adhere))) {
            $ret_check_validate = false;
            $this->errors['type'] = '文件类型不正确，上传失败！';
        }
        return $ret_check_validate;
    }

    /**
     * 上传辅助类的保存功能
     * @param null $save_path
     * @return bool|null
     */
    public function save($save_path = null) {
        if (null === $save_path) {
            $local_path = $this->saveLocal();
            return str_replace(DIRECTORY_SEPARATOR, '/', $local_path);
        } elseif ($this->upload_file_instance->saveAs($save_path)) {
            return $save_path;
        }
        return false;
    }

}