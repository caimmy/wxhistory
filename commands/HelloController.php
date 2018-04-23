<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\AppWxGuanyinqian;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }

    public function actionAdjust() {
        echo "starting...\n";

        $qian_items = AppWxGuanyinqian::find()->all();
        foreach ($qian_items as $qian) {
            if ('签语' == mb_substr($qian->qianyu, 0, 2)) {
                $qian->qianyu = mb_substr($qian->qianyu, 2);
            }
            if ('解签' == mb_substr($qian->jieqian, 0, 2)) {
                $qian->jieqian = mb_substr($qian->jieqian, 2);
            }
            if ('仙机' == mb_substr($qian->xianji, 0, 2)) {
                $qian->xianji = mb_substr($qian->xianji, 2);
            }
            if ('故事' == mb_substr($qian->story, 0, 2)) {
                $qian->story = mb_substr($qian->story, 2);
            }
            $qian->save();
        }
        echo "completed!\n";
    }
}
