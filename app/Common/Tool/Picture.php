<?php

namespace App\Common\Tool;

/*

    imagecopyresized  这个图片压缩的质量有点差速度快；可以尝试使用  imagecopyresampled
    
    传路径不支持改图片输出格式
    \App\Common\Tool\Picture::resize($path='/data/wwwrot/test.png');
    传内容可以必须改图片输出格式
    \App\Common\Tool\Picture::resize($path='xxxxxxxx',$tail='png');



*/

class Picture
{

    /*
        功能：无损压缩 和  等比例压缩  到指定磁盘占用空间以下

        入参
            $path  图片绝对路径，或者图片内容
            $tail  可选参数；如果传了表示直接传的是图片内容，图片格式  jpg  png  gif，用于指定输出图片格式的，不影响图片读取
            $finalsize  无损压缩不传；等比例压缩传压缩到多少字节以下（单位字节）
            $percent 1无损压缩；等比例压缩：0到1的小数（示例0.9）
        返回
            status 1 成功 2 失败
            message  描述
            tail  文件后缀名  png  jpg  gif
            content  压缩后的图片内容
    



    */
    public static function resize($path,$tail=0,$finalsize=0,$percent=1)
    {

        try
        {

// ini_set("display_errors", "On");
// error_reporting(E_ALL | E_STRICT);
            

            if(empty($tail))
            {
                $content=file_get_contents($path);
                $tail=strtolower(pathinfo($path, PATHINFO_EXTENSION));
            }
            else
            {
                $tail=strtolower($tail);
                $content=$path;
            }


            switch($tail)
            {
                case 'jpg':$hanshu='imagejpeg';break;
                case 'png':$hanshu='imagepng';break;
                case 'gif':$hanshu='imagegif';break;
                default:$hanshu=false;
            }


            if(!$hanshu)
            {
                return [
                    'status'=>'2',
                    'message'=>'图片输出格式不支持！'
                ];
            }

            ob_start();

            $str=self::reduce($content,$finalsize,$percent,$hanshu);

            ob_end_clean();        //删除内部缓冲区的内容，关闭缓冲区(不输出)。

            return [
                'status'=>'1',
                'tail'=>$tail,
                'content'=>$str

            ];

          }
          catch(\Exception $e)
          {
// \App\Common\Log::write($_FILES,'attach_pic.txt');
                return [
                    'status'=>'2',
                    'message'=>'系统错误！'.$e->getMessage().$e->getLine()
                ];

          }



    }

    /*

    */
    public static function reduce($content,$finalsize,$percent=0.9,$hanshu)
    { 

        $src_im = imagecreatefromstring($content);

        // 原图宽高
        $w=imagesx($src_im);
        $h=imagesy($src_im);

        // 新图宽高
        $_w=$w*$percent;
        $_h=$h*$percent;

        // 内存溢出的最大值
        $max=3000*2000;

        if($_w*$_h>$max)
        {
            $bi_li=sqrt($max/$_w/$_h);
            $_w=(int)($_w*$bi_li);
            $_h=(int)($_h*$bi_li);
        }




// \App\Common\Log::write($_w.'=='.$_h,'attach_pic.txt');
// return [];

        $dst_im = imagecreatetruecolor($_w, $_h);//这个函数会导致内存溢出[一般出现在图片的宽高过大]
        imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $_w, $_h, $w, $h);//画质不错，速度慢


        $hanshu($dst_im); //输出压缩后的图片

        imagedestroy($dst_im);
        imagedestroy($src_im);

        $length=ob_get_length();  //缓冲区长度
        $str=ob_get_contents();     //返回缓冲区的内容，不输出。
        ob_clean();            //删除内部缓冲区的内容，不关闭缓冲区(不输出)。

        if($percent!=1)
        {
            if($length<$finalsize)
            {
                return $str;
            }
            else
            {
                return self::reduce($str,$finalsize,$percent,$hanshu);
            }

        }
        else
        {
            return $str;

        }
 
    }
}

