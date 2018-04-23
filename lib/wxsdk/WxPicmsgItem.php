<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/4/22
 * Time: 16:51
 */

namespace app\lib\wxsdk;


class WxPicmsgItem
{
    public $Title;
    public $Description;
    public $PicUrl;
    public $Url;

    public function toString() {
        return sprintf('<item><Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]>
</PicUrl><Url><![CDATA[%s]]></Url></item>', $this->Title, $this->Description, $this->PicUrl, $this->Url );
    }
}