<?php 
namespace App\Common\Excel;

class Excel
{
	/*

		将数据写入excel并直接下载


		入参
			表头  一维数组
			表内容 二维数组
			文件名   下载时显示的文件名
		使用示例
			$title=['姓名','出生年','性别'];


			\App\Common\Excel\Excel::downExcel($title,$data,$file_name='下载excel');
	*/
 	public static function downExcel($title,$data,$file_name='excel数据')
 	{
		$objPHPExcel = new \PHPExcel();

/*		$objPHPExcel->getProperties()->setCreator("陈才")
									 ->setLastModifiedBy("陈才")
									 ->setTitle("Office 2007 XLSX 易付生活数据导出")
									 ->setSubject("Office 2007 XLSX 易付生活数据导出")
									 ->setDescription("供易付生活使用的数据导出excel")
									 ->setKeywords("易付生活的数据导出")
									 ->setCategory("export");
*/
		$first_sheet=$objPHPExcel->setActiveSheetIndex(0);
		// $first_sheet->setTitle($sheet_name);

		// 表头
		// $title=['姓名','出生年','性别'];

		$word = ord("A");
		$line=1;
		foreach($title as $v)
		{
		    $column = chr($word).$line;
		    $word++;
			// $first_sheet->setCellValue('A1', 'Hello');
			$first_sheet->setCellValue($column, $v);
		}

		// 表数据
/*		$data=[
			['陈才','1991年','男'],
			['陈才2','1991年','男'],
			['陈才3','1991年','男'],
			['陈才4','1991年','男'],
			['陈才5','1991年','男'],
			['陈才6','1991年','男'],
		];

*/
		$str='';
		foreach($data as $vv)
		{
			$word = ord("A");
			$line++;
			foreach($vv as $vvv)
			{
		    	$column = chr($word).$line;
				$first_sheet->setCellValue($column, $vvv);
				$word++;
			}
		}

		 



		// 下载时的文件名
		// $filename='易付生活数据导出';

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$file_name.'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit();




 	}



 	public static function saveExcel($title,$data,$file_name='excel数据')
 	{
		$objPHPExcel = new \PHPExcel();

		$first_sheet=$objPHPExcel->setActiveSheetIndex(0);
		// $first_sheet->setTitle($sheet_name);

		// 表头
		// $title=['姓名','出生年','性别'];

		$word = ord("A");
		$line=1;
		foreach($title as $v)
		{
		    $column = chr($word).$line;
		    $word++;
			// $first_sheet->setCellValue('A1', 'Hello');
			$first_sheet->setCellValue($column, $v);
		}

		// 表数据
/*		$data=[
			['陈才','1991年','男'],
			['陈才2','1991年','男'],
			['陈才3','1991年','男'],
			['陈才4','1991年','男'],
			['陈才5','1991年','男'],
			['陈才6','1991年','男'],
		];

*/
		$str='';
		foreach($data as $vv)
		{
			$word = ord("A");
			$line++;
			foreach($vv as $vvv)
			{
		    	$column = chr($word).$line;
				$first_sheet->setCellValue($column, $vvv);
				$word++;
			}
		}

		 

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$file_name=$file_name.'.'.date('Y_m_d_H_i_s').'.xlsx';

		$save_path=storage_path().'/excel';

		if(!is_dir($save_path))
		{
			$ok = mkdir($save_path);

			if(!$ok)
			{
				return ['status'=>2,'message'=>'无法创建目录！'];
			}
		}

		$ok = $objWriter->save($save_path.'/'.$file_name);



		// 下载时的文件名
		// $filename='易付生活数据导出';

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$file_name.'"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0



		$objWriter->save('php://output');

		exit();
		
 	}




    //创建一个读取excel数据，可用于入库  
    public static function _readExcel($path)  
    {      

    	/*



        $path=dirname(APP_PATH()).'/test.xlsx';


    $data = \App\Common\Excel\Excel::_readExcel($path);  



*/

    	// echo $path;die;
        //引用PHPexcel 类  
        // include_once(IWEB_PATH.'core/util/PHPExcel.php');  
        // include_once(IWEB_PATH.'core/util/PHPExcel/IOFactory.php');//静态类  
        $type = 'Excel2007';//设置为Excel5代表支持2003或以下版本，Excel2007代表2007版  

       // $xlsReader = \PHPExcel_IOFactory::createReader($type);


        $xlsReader = \PHPExcel_IOFactory::createReader('Excel2007');
        if(!$xlsReader->canRead($path)){
            $xlsReader = \PHPExcel_IOFactory::createReader('Excel5');
        }

        $xlsReader->setReadDataOnly(true);  
        $xlsReader->setLoadSheetsOnly(true);  
        $Sheets = $xlsReader->load($path);  
            //开始读取上传到服务器中的Excel文件，返回一个二维数组  
        $dataArray = $Sheets->getSheet(0)->toArray();  
        return $dataArray;  
    }  



}

