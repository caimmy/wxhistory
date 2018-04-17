<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/4/17
 * Time: 13:39
 */

function debugObj($obj, $terminate=true)
{
    echo '<pre>'.print_r($obj, true).'</pre>';
    if ($terminate)
        Yii::$app->end(0);
}

function recordObj($obj)
{
    file_put_contents(Yii::$app->runtimePath . '/wxdebug.log',
        is_string($obj) ? $obj ."\n" : print_r($obj, true) . "\n", FILE_APPEND);
}