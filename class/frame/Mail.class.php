<?php
/*
    邮件发送类
*/
namespace frame;
require BASEPATH.'/assets/lib/PHPMailer/PHPMailerAutoload.php';
class Mail extends \base{
    //$address可以为邮箱地址也可以是邮箱地址数组  $title 邮件标题  $content 邮件内容
    public function sendmail($address,$title,$content){
        $mail = new \PHPMailer;
		$mail->Charset='UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = $this->App->mail['Host'];
        $mail->Port = $this->App->mail['Port'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->App->mail['Username'];
        $mail->Password = $this->App->mail['Password'];
        $mail->setFrom($this->App->mail['Username'], $this->App->mail['Name']);
        if(is_array($address)){
            foreach($address as $item){
                $mail->addAddress($item);
            }
        }else{
            $mail->addAddress($address);
        }
        $mail->isHTML(true);  
        //$mail->Subject = $title;
		$mail->Subject = "=?utf-8?B?" . base64_encode($title) . "?=";
        $mail->Body=$content.'<div width="720" style="padding-top:5px;margin-top:5px; border-top:1px dashed #e5e5e5;text-align:center;color:#999999; font-size:12px;font-family:\'Microsoft YaHei\';">此为系统邮件请勿回复</div>';
        if($mail->send()){
           return true;
        } else {
           return false;
        }

    }
}