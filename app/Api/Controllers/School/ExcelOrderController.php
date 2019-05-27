<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/20
 * Time: 下午3:18
 */

namespace App\Api\Controllers\School;
use Illuminate\Support\Facades\DB;


use App\Api\Controllers\BaseController;
use App\Models\Order;
use Illuminate\Http\Request;

class ExcelOrderController extends BaseController
{



    private function makeOrderNo(){
        return  date('YmdHis').mt_rand(10000,99999);
    }

    private function makeNo(){
        return str_random(12);
    }
    public function import(Request $request)
    {
        try {
            $loginer = $this->parseToken();
            $merchant_id=$loginer->merchant_id;

            $store_id = $request->get('store_id');

            $gmt_end=$request->get('gmt_end');
            $gmt_start=date('Y-m-d H:i:s');
                $this->status=2;

            if(!strtotime($gmt_end))
            {
                $this->message='缴费截止时间不正确！';
                return $this->format();

            }

            if(strtotime($gmt_end) < time())
            {

                $this->message='缴费截止时间不正确！';
                return $this->format();
            }



            if(empty($_FILES))
            {
                $this->message='请上传xlsx表格！';
                return $this->format();
            }

            $file_arr=array_shift($_FILES);

            if($file_arr['error'] !==0)
            {

                $this->message='请上传xlsx表格！';
                return $this->format();
            }
            // var_dump($file_arr);die;
            $file=$file_arr['tmp_name'];





            $final_ok=false;

            $excel_data = \App\Common\Excel\Excel::_readExcel($file);

            $item_name=[
                // 'excel下标编号'=>小项等名称,
            ];
            foreach($excel_data as $k=>$v)
            {
// 学校和缴费项目
                if($k==1){
                    $school_name=trim($v[0]);
                    $batch_name=trim($v[1]);

                    $have_school = \App\Models\StuStore::where('school_name',$school_name)->first();
                    if(empty($have_school))
                    {
                        $this->message='学校不存在！请检测学校名称是否准确！';
                        return $this->format();
                    }

                    $store_id = $have_school->store_id;

                    $have_pay_item = \App\Models\StuOrderBatch::where('batch_name',$batch_name)->first();
                    if(!empty($have_pay_item))
                    {
                        $this->message='收费项目名称已经存在，请更换项目名称！';
                        return $this->format();
                    }

//项目入库
DB::beginTransaction();

                    $batch_no=$this->makeNo();
                    $ok=\App\Models\StuOrderBatch::create([
                            'stu_order_batch_no'=>$batch_no,
                            'store_id'=>$store_id,
                            'merchant_id'=>$merchant_id,
                            'batch_name'=>$batch_name,
                            'batch_amount'=>0,
                            'batch_item'=>'',
                            'stu_grades_no'=>'',
                            'stu_class_no'=>'',
                            'stu_order_type_no'=>'',
                            'gmt_end'=>$gmt_end,
                            'batch_create_type'=>'2',
                            'status'=>1,
                            'status_desc'=>'审核通过',
                            'remove_student_no'=>''
                        ]);

                    if(!$ok)
                    {

                        $this->message='收费项目创建失败，请重试！';
                        return $this->format();
                    }
                }


// 缴费项目名称按照在excel的位置记录
                if($k==3){
                    foreach($v as $kk=>$vv)
                    {
                        // 表格标题名称全部记录
                        $item_name[$kk]=$vv;
                    }
                }

                if($k<=3)
                {
                    continue;
                }

$temp_total_amount=0;

                $temp_item=[];
                $temp_item_b=[];
                foreach($v as $kkk=>$vvv)
                {

                    // 在excel的下标是第6个开始是小项
                    if($kkk >=6 && $vvv != 0)
                    {

                        if($vvv>0)
                        {
                            $temp_item[$item_name[$kkk]]=$vvv;
                        }else{
                            $temp_item_b[$item_name[$kkk]]=$vvv;
                        }
                            $temp_total_amount+=abs($vvv);

                    }
                }


                if(empty(array_merge($temp_item,$temp_item_b)))
                {
                    throw new \Exception('缴费小项不存在！');
                }

// 学生  和缴费项目
                $out_trade_no=$this->makeOrderNo();

                $grade_name=trim($v[0]);
                $class_name=trim($v[1]);
                $student_name=trim($v[2]);
                $student_user_mobile=trim($v[3]);
                $student_user_name=trim($v[4]);


                // 查找年级编号、班级编号
                $grade = \App\Models\StuGrade::where('stu_grades_name',$grade_name)->where('store_id',$have_school->store_id)->first();
                if(empty($grade))
                {
                    throw new \Exception('年级不存在！');
                }

                $class = \App\Models\StuClass::where('stu_grades_no',$grade->stu_grades_no)->where('store_id',$have_school->store_id)->where('stu_class_name',$class_name)->first();

                if(empty($class))
                {
                    throw new \Exception('班级不存在！');
                }

                // 查找学生信息  根据年级、班级、学生姓名、家长手机号
                $student = \App\Models\StuStudent::where('store_id',$have_school->store_id)->where('stu_grades_no',$grade->stu_grades_no)
                    ->where('stu_class_no',$class->stu_class_no)
                    ->where('student_name',$student_name)
                    ->where('student_user_mobile',$student_user_mobile)
                    ->first();

                if(empty($student))
                {
                    throw new \Exception('学生['.$student_name.']不存在！');
                }


                // 创建大订单
                $dbdata=[
                    'user_id'=>$have_school->user_id,//店铺推广员id
                    'school_name'=>$have_school->school_name,// 
                    'batch_name'=>$batch_name,// 

                    'stu_grades_name'=>$grade->stu_grades_name,// 
                    'stu_class_name'=>$class->stu_class_name,// 
                    'gmt_start'=>$gmt_start,
                    // 'pay_time'=>$pay_time,

                    'trade_no'=>'',
                    'pay_type_source'=>'',
                    'pay_type_source_desc'=>'',
                    
                    'pay_type'=>'',//支付类型
                    'pay_type_desc'=>'',//支付类型描述

                        'out_trade_no'=>$out_trade_no,
                        'store_id'=>$student->store_id,
                        'student_user_name'=>$student->student_user_name,
                        'student_user_mobile'=>$student->student_user_mobile,
                        'merchant_id'=>$merchant_id,
                        'stu_grades_no'=>$student->stu_grades_no,
                        'stu_class_no'=>$student->stu_class_no,
                        'student_no'=>$student->student_no,
                        'student_name'=>$student->student_name,
                        'stu_order_batch_no'=>$batch_no,
                        'stu_order_type_no'=>'',
                        'amount'=>$temp_total_amount,
                        'pay_amount'=>0,
                        'gmt_end'=>$gmt_end,
                        'pay_status'=>2,
                        'pay_status_desc'=>'未支付',

                    'order_create_type'=>2

                ];

                $ok = \App\Models\StuOrder::create($dbdata);
                if(!$ok)
                {
                    throw new \Exception('订单创建失败！');
                }


                // 创建小项订单
                $item_serial_number=1;


                foreach($temp_item as $name=>$money)
                {
                    $ok = \App\Models\StuOrderItem::create([
                            'out_trade_no'=>$out_trade_no,
                            'item_serial_number'=>$item_serial_number++,
                            'item_name'=>$name,
                            'item_price'=>abs($money),
                            'item_mandatory'=>'Y',
                            'item_number'=>1,
                            'status'=>2,
                            'status_desc'=>'待支付'
                        ]);

                    if(!$ok)
                    {
                        throw new \Exception('小项订单创建失败！');
                    }
                }


                foreach($temp_item_b as $name=>$money)
                {
                    $ok = \App\Models\StuOrderItem::create([
                            'out_trade_no'=>$out_trade_no,
                            'item_serial_number'=>$item_serial_number++,
                            'item_name'=>$name,
                            'item_price'=>abs($money),
                            'item_mandatory'=>'N',
                            'item_number'=>1,
                            'status'=>2,
                            'status_desc'=>'待支付'
                        ]);

                    if(!$ok)
                    {
                        throw new \Exception('小项订单创建失败！');
                    }
                }


            $final_ok=true;

        }


        if(!$final_ok)
        {
            throw new \Exception('excel表格数据可能不正确，请核对后重试！');
        }

DB::commit();


            $this->status = 1;
            $this->message = '缴费订单已经全部创建成功！';
            return $this->format();

        } catch (\Exception $exception) {
DB::rollBack();
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

}