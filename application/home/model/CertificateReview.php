<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/20
 * Time: 10:39
 */
namespace app\home\model;
use think\Model;
use think\Collection;

class CertificateReview extends Model
{
    const TYPE_CERTIFICATE = 1;
    const TYPE_ARRAY = [
        self::TYPE_CERTIFICATE  => '三证审核',
    ];

    public $insert = [
        'status' => 0,
        'recommend' => 1,
        'create_time' => NOW_TIME,
    ];

}