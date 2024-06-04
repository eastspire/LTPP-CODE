<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: ltpp-universe 1491579574@qq.com
 * @LastEditTime: 2023-11-12 01:01:51
 * @FilePath: \LTPP-CODE\app\controller\Email.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email extends Image
{
    /**
     * 邮件发送函数
     * @param string $to 接收者邮箱
     * @param string $title 邮件标题
     * @param string $content 邮件内容
     */
    static public function mailto($to = '', $title = '', $content = '')
    {
        try {
            $offline = (int) Base::getSettingKeyData('offline');
            if ($offline == 1) {
                return;
            }
            $useqqmail = (int) Base::getSettingKeyData('useqqmail');
            if ($useqqmail == 1) {
                $smtpemail = Base::getSettingKeyData('smtp');
                $smtpkey = Base::getSettingKeyData('smtpkey');
                if (!$smtpemail || !$smtpemail) {
                    return;
                }
                $mail = new PHPMailer(true);
                $mail->SMTPDebug = 0; //取消debug，防止输出影响结果
                $mail->isSMTP();
                $mail->Host = 'smtp.qq.com'; //qq邮箱的服务器地址
                $mail->SMTPAuth = true;
                $mail->Username = $smtpemail; //授权的qq邮箱
                $mail->Password = $smtpkey; //qq授权码，不是密码！！！
                $mail->SMTPSecure = 'ssl'; // 使用 ssl 加密方式登录boolean
                $mail->Port = 465; //smtp 服务器的远程服务器端口号
                //Recipients
                $mail->setFrom($smtpemail, $to); //授权的qq邮箱（和上面一样），自己起的昵称
                $mail->addAddress($to); // 传过来的收件人
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = $title; //传过来的标题
                $mail->Body = $content; //传过来的内容
                $mail->send();
            } else {
                $mail_url = Base::getSettingKeyData('mysmtpurl');
                $mail_username = Base::getSettingKeyData('mysmtpname');
                $mail_password = Base::getSettingKeyData('mysmtppassword');
                if (!$mail_url || !$mail_username) {
                    return;
                }
                Base::postRequest($mail_url, ['Content-Type:application/x-www-form-urlencoded'], [
                    'mail_from' => $mail_username,
                    'password' => $mail_password,
                    'mail_to' => $to,
                    'subject' => $title,
                    'content' => $content,
                    'subtype' => 'html'
                ]);
            }
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '邮件异常信息：' . "\n" . $e->getMessage());
        }
        return;
    }
}
