<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;
use Webman\RedisQueue\Redis as RedisQueue;

class Register extends Email
{
    /**
     * 注册判断
     * @param Request $request 请求
     * @return string $res json
     */
    public function judgeRegister(Request $request)
    {
        $canregister = Base::getSettingKeyData('canregister');
        if ($canregister != 1) {
            return \json(['code' => -1, 'msg' => 'root关闭了注册通道！请联系root用户申请账号！']);
        }

        $name = $request->post('name');
        if (strlen($name) > 18) {
            return \json(['code' => -1, 'msg' => '用户名长度不能大于18']);
        }
        $nullname = $name;
        $temname = preg_replace('/ /', '', $nullname);
        if ($temname == '') {
            return \json(['code' => -1, 'msg' => '用户名不能为空']);
        }
        $email = $request->post('email');
        $nullemail = $request->post('email');
        $tememail = preg_replace('/ /', '', $nullemail);
        if ($tememail == '') {
            return \json(['code' => -1, 'msg' => '邮箱不能为空']);
        }
        if (strripos($email, '@qq.com') === false) {
            return \json(['code' => -1, 'msg' => '邮箱请填写QQ邮箱']);
        }
        $tempassword = $request->post('password');
        $tempasswd = preg_replace('/ /', '', $tempassword);
        if ($tempasswd == '') {
            return \json(['code' => -1, 'msg' => '密码不能为空']);
        }
        $redis2 = Redis::connection('db2');
        //缓存存在
        if ($redis2->get('ErrorVerification' . $name . $tememail)) {
            return json(['code' => -1, 'msg' => '30秒后可重新验证！']);
        }
        $judge_name = Db::table('user')
            ->where('name', $name)
            ->where('isdel', 0)
            ->exists();

        if ($judge_name) { //用户名已存在，无法注册
            return json(['code' => -1, 'msg' => '用户名已存在，无法注册']);
        }

        $epasswd = $request->post('password');
        $password = Base::passwordEncryption($epasswd); //密码加密两次

        $UserVerification = $request->post("code");

        $sex = $request->post("sex");
        $now = date('Y-m-d H:i:s', time());
        $data = [
            'name' => $name,
            'password' => $password,
            'sex' => $sex,
            'registertime' => $now,
            'headimage' => 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . $email . '&spec=640',
            'fans' => 0,
            'follow' => 0,
            'online' => 0,
            'grade' => 1,
            'email' => $email,
            'school' => $request->post("school") ?? '无',
            'enrollment_year' => $request->post("enrollmentyear") ?? 0,
            'subject' => $request->post("subject") ?? '无',
            'class' => $request->post("class") ?? '无',
            'money' => 1
        ];

        $redis13 = Redis::connection('db13');

        //获取数据库验证码
        $JudgeVerification = $redis13->get($name);

        if (!$JudgeVerification) {
            return json(['code' => -3, 'msg' => '验证码不存在或已过期！']);
        }
        if ($UserVerification != $redis13->get($name)) {
            $redis2->setEx('ErrorVerification' . $name . $tememail, 30, 1);
            return json(['code' => -3, 'msg' => '验证码错误！30秒后可再次尝试！']);
        }
        $my_aid = Base::insertToDb('user', $data);
        if ($my_aid) {
            Base::updateUserDataRedis($my_aid);
            // 云盘创建README文件
            $my_uid = Base::getChatUserUidById($my_aid);
            $readme_file_path = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid;
            Cloudfile::creatFile($readme_file_path);
            $offline = (int) Base::getSettingKeyData('offline');
            if ($offline == 0) {
                $Zcontent = '您的账号：';
                $Mcontent = '您的密码：';
                $content = "$Zcontent $name\n$Mcontent $epasswd\n
请勿将密码泄露给他人\n
如不同意以下协议请停止使用本产品\n                    
LTPP在线开发平台许可协议\n
请务必认真阅读和理解本《LTPP在线开发平台许可协议》 ( 以下简称《协议》 ) 中规定的所有权利和限制。除非您接受本《协议》条款，否则您无权下载、安装或使用“LTPP”软件及其相关服务。您一旦安装、复制、下载、访问或以其它方式使用本软件产品，将视为对本《协议》的接受，即表示您同意接受本《协议》各项条款的约束。如果您不同意本《协议》中的条款，请不要安装、复制或使用本软件。\n
一 . 权利声明\n
“LTPP”由LTPP开发者（邮箱：1491579574@qq.com）自主开发，LTPP软件的一切知识产权，以及与“LTPP”软件相关的所有信息内容，包括但不限于：文字表述及其组合、图标、图饰、图像、图表、色彩、界面设计、版面框架、有关数据、附加程序、印刷材料或电子文档等均为LTPP所有，受著作权法和国际著作权条约以及其他知识产权法律法规的保护。\n
二 . 许可范围\n
2.1 下载、安装和使用：本软件为免费下载使用，用户可以非商业性、无限制数量地下载、安装及使用本软件。\n
2.2 复制、分发和传播：用户可以非商业性、无限制数量地复制、分发和传播本软件产品。但必须保证每一份复制、分发和传播都是完整和真实的 , 包括所有有关本软件产品的软件、电子文档 , 版权和商标，亦包括本协议。\n
三 . 权利限制\n
3.1 禁止反向工程、反向编译和反向汇编：用户不得对本软件产品进行反向工程 (Reverse Engineer) 、反向编译 (Decompile) 或反向汇编 (Disassemble) ，同时不得改动编译在程序文件内部的任何资源。除法律、法规明文规定允许上述活动外，用户必须遵守此协议限制。\n
3.2 保留权利：本协议未明示授权的其他一切权利仍归LTPP开发者（邮箱：1491579574@qq.com）所有，用户使用其他权利时必须获得LTPP开发者（邮箱：1491579574@qq.com）的书面同意。\n
四 . 用户使用须知\n
4.1 本软件提供分为服务器端 agent 和 PC 控制台两部分，其中服务器端 agent 主要功能为服务器安全防护， PC 控制台主要功能为远程管理和监控。服务器端 agent 适用于 linux/windows 操作系统； PC 控制台支持 window XP 以上操作系统。如果用户在安装本软件后因任何原因欲放弃使用，可删除本软件。\n
4.2 本软件由LTPP开发者（邮箱：1491579574@qq.com）提供产品支持。\n
4.3 软件的修改和升级：LTPP保留为用户提供本软件的修改、升级版本的权利。\n
4.4 用户应在遵守法律及本协议的前提下使用本软件。\n
4.4.1 不得故意避开或者破坏著作权人为保护本软件著作权而采取的技术措施 ;\n
4.4.2 用户不得利用本软件误导、欺骗他人 ;\n
4.4.3 违反国家规定，对计算机信息系统功能进行删除、修改、增加、干扰，造成计算机信息系统不能正常运行，\n
4.4.4 其他任何危害计算机信息网络安全的。\n
4.5 LTPP的唯一官网为 https://ltpp.vip , 对于从非LTPP指定站点下载的本软件产品以及从非LTPP发行的介质上获得的本软件产品，LTPP无法保证该软件是否具有风险，使用此类软件，将可能导致不可预测的风险，建议用户不要轻易下载、安装、使用，LTPP不承担任何由此产生的一切法律责任。\n
五 . 免责与责任限制\n
5.1 本软件经过详细的测试，但不能保证与所有的软硬件系统完全兼容，不能保证本软件完全没有错误。如果出现不兼容及软件错误的情况，用户可登录LTPP官网论坛、LTPP QQ 群将情况报告LTPP官方，获得技术支持。如果无法解决兼容性问题，用户可以删除本软件。\n
5.2 使用本软件产品风险由用户自行承担，在适用法律允许的最大范围内，对因使用或不能使用本软件所产生的损害及风险，包括但不限于直接或间接的个人损害、商业赢利的丧失、贸易中断、商业信息的丢失或任何其它经济损失，LTPP不承担任何责任。\n
5.3 对于因电信系统或互联网网络故障、计算机故障或病毒、信息损坏或丢失、计算机系统问题或其它任何不可抗力原因而产生损失，LTPP不承担任何责任。\n
5.4 用户违反本协议规定，对LTPP造成损害的。LTPP有权采取包括但不限于中断使用许可、停止提供服务、限制使用、法律追究等措施。\n
六 . 法律及争议解决\n
6.1 本协议适用中华人民共和国法律。\n
6.2 因本协议引起的或与本协议有关的任何争议，各方应友好协商解决 ; 协商不成的，任何一方均可将有关争议提交至北京仲裁委员会并按照其届时有效的仲裁规则仲裁 ; 仲裁裁决是终局的，对各方均有约束力。\n
七 . 其他条款\n
7.1 如果本协议中的任何条款无论因何种原因完全或部分无效或不具有执行力，或违反任何适用的法律，则该条款被视为删除，但本协议的其余条款仍应有效并且有约束力。\n
7.2 LTPP有权根据有关法律、法规的变化以及公司经营状况和经营策略的调整等修改本协议。修改后的协议会随附于新版本软件。当发生有关争议时，以最新的协议文本为准。如果不同意改动的内容，用户可以自行删除本软件。如果用户继续使用本软件，则视为您接受本协议的变动。\n
7.3 本协议的一切解释权与修改权归LTPP。\n";
                RedisQueue::send(Base::$redis_queue_send_mail_name, [
                    'to' => $email,
                    'title' => 'LTPP账号注册成功',
                    'content' => $content
                ]);
                Robot::sendChatToOneUserMsg($my_aid, '## LTPP账号注册成功' . "\n" . $content);
                Robot::sendChatToOneUserMsg(Base::getRootId(), '**【' . $now . '】** 用户 **【' . $name . '】** 注册成功');
            }
            return json(['code' => 1, 'msg' => '账号注册成功']);
        }
        return json(['code' => 0, 'msg' => '账号注册失败']);
    }
}
;