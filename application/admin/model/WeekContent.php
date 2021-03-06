<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/19
 * Time: 15:57
 */
namespace app\admin\model;
use think\Model;
use think\Collection;

class WeekContent extends Base
{
    //负荷
    const LOAD_ONE = 1;
    const LOAD_TWO = 2;
    const LOAD_THREE = 3;
    const LOAD_FOUR = 4;
    const LOAD_FIVE = 5;
    const LOAD_ARRAY = [
        self::LOAD_ONE  => '低',
        self::LOAD_TWO  => '中低',
        self::LOAD_THREE  => '中',
        self::LOAD_FOUR  => '中高',
        self::LOAD_FIVE  => '高',
    ];
    //时间
    const DURATION_ONE = 1;
    const DURATION_TWO = 2;
    const DURATION_THREE = 3;
    const DURATION_FOUR = 4;
    const DURATION_FIVE = 5;
    const DURATION_ARRAY = [
        self::DURATION_ONE  => '1',
        self::DURATION_TWO  => '1.5',
        self::DURATION_THREE  => '2',
        self::DURATION_FOUR  => '2.5',
        self::DURATION_FIVE  => '3',
    ];
    public $insert = [
        'create_time' => NOW_TIME,
    ];

}