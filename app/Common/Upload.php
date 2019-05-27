<?php 

namespace App\Common;

class Upload
{

  public static $img_type=['jpg','png','gif'];
  public static $file_type=['zip','rar'];


	/*
    附件上传(图片或者文件)
    只区分前台还是后台：前台public/upload/xxx.zip   后台upload/xxx.zip


    type为1表示前台图片  需要web链接
    type为2表示后台图片，不要web链接

	*/
	public static function index($type=1)
	{ 

// \App\Common\Log::write($_FILES,'attach_pic.txt');


      try
      {


            if(empty($_FILES))
            {
                    return [
                        'status'=>'2',
                        'message'=>'上传的附件不存在！'
                    ];
            }

            $file=current($_FILES);
/*
            var_dump(pathinfo($file['name']));

            var_dump($file);die;*/

            $base_path = base_path();


    /*


    array(4) {
      ["dirname"]=>
      string(1) "."
      ["basename"]=>
      string(8) "test.jpg"
      ["extension"]=>
      string(3) "jpg"
      ["filename"]=>
      string(4) "test"
    }
    */
            $file_name_arr=pathinfo($file['name']);

            $file_name=$file_name_arr['filename'];
            $file_tail=$file_name_arr['extension'];


            if(!in_array($file_tail, self::$img_type) && !in_array($file_tail, self::$file_type))
            {
              return [
                  'status'=>'2',
                  'message'=>'附件格式不支持！'
              ];

            }


            //   upload/attach/2018/.../xx.zip
            //   upload/image/2018/.../xx.jpg


            $mid_dir_name='attach';
            // 图片目录
            if(in_array($file_tail, self::$img_type))
            {
              $mid_dir_name='image';
            }

            $make_relate_path='upload/'.$mid_dir_name.'/'.date('Y/m/d');

            $new_file_name = date('His').'_' . mt_rand(100, 999) . '.' . $file_tail;


            //前台
            if($type==1)
            {
              $web_path=$make_relate_path.'/'.$new_file_name;//http://www.baidu.com/2018/xxx.jpg    这里是2018/xx.jpg
              $disk_dir=base_path().'/public/'.$make_relate_path;
              $disk_relate_path='public/'.$make_relate_path.'/'.$new_file_name;
            }
            //后台
            else
            {
              $web_path='';
              $disk_dir=base_path().'/'.$make_relate_path;
              $disk_relate_path=$make_relate_path.'/'.$new_file_name;
            }


            $ok=true;
            !is_dir($disk_dir) && $ok=mkdir($disk_dir,0777,true);
            if($ok===false)
            {
              return [
                  'status'=>'2',
                  'message'=>'服务器权限不够！'
              ];
            }

            $move = move_uploaded_file($file['tmp_name'], base_path().'/'.$disk_relate_path);

            if($move)
            {
              return ['status'=>1,'web_path'=>$web_path,'disk_relate_path'=>$disk_relate_path];
            }
            else
            {
              return [
                  'status'=>'2',
                  'message'=>'附件上传失败！'
              ];

            }



      }
      catch(\Exception $e)
      {
          	return [
                'status'=>'2',
                'message'=>'系统错误！'.$e->getMessage().$e->getLine()
          	];
      }

	}
}









 ?>