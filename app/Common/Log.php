<?php
namespace App\Common;
class Log
{
	public static $max_file_size=1*1024*1024;//不大于2M

	/*
		传入log文件绝对路径
	*/
	public static function write($data,$file='')
	{
		if(empty($file))
		{
			$file=storage_path().'/logs/log.txt';
		}
		else
		{
			$file=storage_path().'/logs/'.$file;
		}
		$check=self::checkFile($file);
		if($check)
		{
			self::renameFile($file);
		}

		$str = "\n";
		$str .= "\n";
		$str .='============='.date('Y-m-d H:i:s').'======================'."\n";
		if(is_string($data))
			$str.=$data;
		else
			$str.=var_export($data,true);

		file_put_contents($file, $str,FILE_APPEND);
	}


	public static function renameFile($file)
	{

		$info=pathinfo($file);//array ( 'dirname' => './s', 'basename' => 'chencai.log.txt', 'extension' => 'txt', 'filename' => 'chencai.log', )

		$newfile=$info['dirname'].'/'.$info['filename'].'_'.date('YmdHis').'_'.mt_rand(100000,999999).'.'.$info['extension'];
		rename($file, $newfile);

	}

	// 如果超过规定日志大小，返回true；否则返回false
	public static function checkFile($file)
	{
		clearstatcache();
		if(file_exists($file)&&(filesize($file)>self::$max_file_size))
		{
			return true;
		}
		return false;
	}

}
/*

while(1)
{
	$data='successsuccesssuccesssuccesssuccesssuccesssuccesssuccesssuccess';
	$file='./log.txt';
	Log::write($data,$file);	
}

*/

