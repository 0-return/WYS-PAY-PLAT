<?php

namespace App\Api\Controllers\School;
use Illuminate\Support\Facades\DB;

/*
*/
class TeacherController extends \App\Api\Controllers\BaseController
{

    public function typelst(){
        $this->status=1;
        $data=[
            ['name'=>'班主任','type'=>1],
            ['name'=>'老师','type'=>2],
        ];
         return $this->format($data);
    }


    /*
        列表
    */
    public function lst(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

                $all_store_id=[];


            if(!empty($request->get('store_id')))
            {
                $all_store_id=[$request->get('store_id')];
            }else{

                $get_all_store_id = \App\Models\MerchantStore::where('merchant_id',$loginer->merchant_id)->get();
                foreach($get_all_store_id as $v)
                {
                    $all_store_id[]=$v->store_id;
                }


            }

                $get_all_school=\App\Models\StuStore::get();
                foreach($get_all_school as $v)
                {
                    $all_school[$v->store_id]=$v->school_name;
                }


            $obj=\App\Models\StuMerchantClass::select(DB::raw('
                    merchants.name,
                    merchants.phone,
                    stu_merchant_class.store_id,
                    stu_merchant_class.type,
                    merchants.id as merchant_id
                '))
            ->leftJoin('merchants','merchants.id','stu_merchant_class.merchant_id')
            ->whereIn('stu_merchant_class.store_id',$all_store_id);


            if(!empty($request->get('name')))
            {
                $obj=$obj->where('merchants.name',$request->get('name'));
            }

            if(!empty($request->get('stu_class_no')))
            {
                $obj=$obj->where('stu_merchant_class.stu_class_no',$request->get('stu_class_no'));
            }




/*
            $grade =  new \App\Models\StuClass;

            $grade = $grade->where('stu_grades_no',$request->get('stu_grades_no'));
*/
            $this->t=$obj->count();

            $data=$this->page($obj)->get();
            $cout=[];

            if(!$data->isEmpty())
            {
                $cout=array_map(function($each) use($all_school){
                    return array_merge($each,[
                        'school_name'=>isset($all_school[$each['store_id']]) ? $all_school[$each['store_id']] :'' ,
                        'type_name'=>$each['type']==3 ?'班主任':'教师'
                        ] );
                },$data->toArray());
            }



            $this->status=1;
            $this->message='ok';
            return $this->format($cout);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }



    /*
        教师和班级关联
    */
    public function relate(){
        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;

            $cin=[
                'store_id'=>$request->get('store_id'),
                'merchant_id'=>$request->get('merchant_id'),
                'type'=>$request->get('type'),
                'stu_class_no'=>$request->get('stu_class_no'),
            ];



              $validate=\Validator::make($cin, [
                        'store_id'=>'required|exists:stu_stores,store_id',
                        'merchant_id'=>'required|exists:merchants,id',
                        'type'=>'required',
                        'stu_class_no'=>'required|exists:stu_class,stu_class_no',
                    // 'cate_id'=>'required|exists:goods_cate,id',
              ], [
                  'required' => ':attribute为必填项！',
                  'min' => ':attribute长度不符合要求！',
                  'max' => ':attribute长度不符合要求！',
                  'unique' => ':attribute已经被人占用！',
                  'exists' => ':attribute不存在！'
              ], [
                        'store_id'=>'学校编号',
                        'merchant_id'=>'教师编号',

                        'type'=>'教师类型',
                        'stu_class_no'=>'班级编号',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }


          if(!in_array($cin['type'], [1,2])){

            $this->message='教师类型不正确';
            return $this->format();
          }

          $cin['type']==1 ? $cin['type']=3 : '';



        $have = \App\Models\StuMerchantClass::where('store_id',$cin['store_id'])->where('merchant_id',$cin['merchant_id'])->where('type',$cin['type'])->first();

        if(!empty($have))
        {
            $this->message='关联项已经存在！';
            return $this->format();
        }

        \App\Models\StuMerchantClass::create($cin);

            $this->status=1;
            $this->message='关联项创建成功';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }






    /*
        教师和班级取消关联
    */
    public function unbind(){
        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;

            $cin=[
                'store_id'=>$request->get('store_id'),
                'merchant_id'=>$request->get('merchant_id'),
                'type'=>$request->get('type'),
                'stu_class_no'=>$request->get('stu_class_no'),
            ];



              $validate=\Validator::make($cin, [
                        'store_id'=>'required',
                        'merchant_id'=>'required',
                        'type'=>'required',
                        'stu_class_no'=>'required',
                    // 'cate_id'=>'required|exists:goods_cate,id',
              ], [
                  'required' => ':attribute为必填项！',
                  'min' => ':attribute长度不符合要求！',
                  'max' => ':attribute长度不符合要求！',
                  'unique' => ':attribute已经被人占用！',
                  'exists' => ':attribute不存在！'
              ], [
                        'store_id'=>'学校编号',
                        'merchant_id'=>'教师编号',

                        'type'=>'教师类型',
                        'stu_class_no'=>'班级编号',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }


          if(!in_array($cin['type'], [1,2])){

            $this->message='教师类型不正确';
            return $this->format();
          }



        $ok = \App\Models\StuMerchantClass::where('store_id',$cin['store_id'])->where('merchant_id',$cin['merchant_id'])->where('stu_class_no',$cin['stu_class_no'])->where('type',$cin['type'])->delete();

            $this->status=1;
            $this->message='已解除关联！';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }







    /*
        当前登录教师的资料
    */
    public function loginerInfo(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));//merchant_name  姓名
            $this->status=2;


            $logo='';
            $merchant=\App\Models\Merchant::where('id',$loginer->merchant_id)->first();
            if(!empty($merchant))
            {
                $logo=$merchant->logo;
            }


            $store_id='';
            $get_mid=\App\Models\StuMerchantClass::where('merchant_id',$loginer->merchant_id)->orderBy('type','desc')->get();
            $teacher_type=2;;
            $teacher_type_name='';
            $school=null;




            // 班级以及教师职称
                $all_class=[];
                foreach($get_mid as $v)
                {
                    if(!isset($all_class[$v->stu_class_no]))
                        $all_class[$v->stu_class_no]= $v->type;
                }
// insert into stu_merchant_class (`store_id`,`merchant_id`,`type`,`stu_class_no`) values('2018061205492993161','1','2','5rT8L4fx');
// insert into stu_merchant_class (`store_id`,`merchant_id`,`type`,`stu_class_no`) values('2018061205492993161','1','2','58ys7Eom');


                $cout=[];

                if(!empty($all_class))
                {

                    $get_data=\App\Models\StuClass::leftJoin('stu_grades','stu_grades.stu_grades_no','stu_class.stu_grades_no')->whereIn('stu_class.stu_class_no',array_keys($all_class))->get();

                    foreach($get_data as $v)
                    {
                        $cout[]=[
                            'stu_grades_no'=>$v->stu_grades_no,
                            'stu_class_no'=>$v->stu_class_no,
                            'stu_class_name'=>$v->stu_class_name,
                            'stu_grades_name'=>$v->stu_grades_name,
                            // 在当前班级的职称
                            'type'=>isset($all_class[$v->stu_class_no]) ? $all_class[$v->stu_class_no] : '',
                            'type_name'=>(isset($all_class[$v->stu_class_no]) && $all_class[$v->stu_class_no]==3) ? '班主任' : '教师'
                        ];
                    }

              
                }


            $grade_name='';
            $class_name='';
            if(!$get_mid->isEmpty())
            {
                $mid=$get_mid[0];

                $school = \App\Models\StuStore::where('store_id',$mid->store_id)->first();
                $teacher_type = $mid->type;
                $store_id=$mid->store_id;
            }
            $teacher_type_name = ($teacher_type==3) ? '班主任' : '教师';




            $data=[
                'store_id'=>$store_id,
                'school_name'=>empty($school) ? '' :$school->school_name,
                
                'name'=>$loginer->merchant_name,
                'type'=>$teacher_type,
                'type_name'=>$teacher_type_name,
                'logo'=>$logo,

                'all_class'=>$cout
            ];


            $this->status=1;
            $this->message='ok';
            return $this->format($data);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }





    /*
    */
    public function loginerClassLst(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $mid=\App\Models\StuMerchantClass::where('merchant_id',$loginer->merchant_id)->get();

            if(empty($mid))
            {
                $this->message='当前教师未分配班级。';
                return $this->format();
            }
            $all_class=[];
            foreach($mid as $v)
            {
                $all_class[]=$v->stu_class_no;
            }

            $data=\App\Models\StuClass::whereIn('stu_class_no',$all_class)->get();
            if(($data->isEmpty()))
            {
                $this->message='当前教师未分配班级。';
                return $this->format();
            }

            $this->status=1;
            $this->message='ok';
            return $this->format($data);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }






























































































    private function makeClassNo(){
        return '5'.str_random(7);
    }
    /*
        添加
    */
    public function add(){
        die;

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;

            $have = \App\Models\StuClass::where('store_id',$request->get('store_id'))->where('stu_class_name',$request->get('stu_class_name'))->first();

            if(!empty($have))
            {
                $this->message='班级已经存在！';
                return $this->format();
            }
            $cin=[
                'merchant_id'=>$merchant_id,//创建者id

                'store_id'=>$request->get('store_id',''),
                'stu_grades_no'=>$request->get('stu_grades_no',''),

                'stu_class_no'=>$this->makeClassNo(),
                'stu_class_name'=>$request->get('stu_class_name',''),
                'stu_class_desc'=>$request->get('stu_class_desc',''),
            ];
 

              $validate=\Validator::make($cin, [
                        'store_id'=>'required',
                        'stu_grades_no'=>'required',
                        'stu_class_no'=>'required',
                        'stu_class_name'=>'required',
                    // 'cate_id'=>'required|exists:goods_cate,id',
              ], [
                  'required' => ':attribute为必填项！',
                  'min' => ':attribute长度不符合要求！',
                  'max' => ':attribute长度不符合要求！',
                  'unique' => ':attribute已经被人占用！',
                  'exists' => ':attribute不存在！'
              ], [
                        'stu_grades_no'=>'年级编号',
                        'store_id'=>'学校编号',

                        'stu_class_no'=>'年级编号',
                        'stu_class_name'=>'年级名称',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }

          $grade=\App\Models\StuClass::create($cin);


            $this->status=1;
            $this->message='班级创建成功';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }



    /*
        修改
    */
    public function save(){
        die;

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;

          $class=\App\Models\StuClass::where('stu_class_no',$request->get('stu_class_no'))->first();

          if(empty($class))
          {
            $this->message='班级不存在！';
            return  $this->format();
          }

            $cin=[
                'store_id'=>$request->get('store_id',''),
                'stu_grades_no'=>$request->get('stu_grades_no',''),
                'stu_class_name'=>$request->get('stu_class_name',''),
                'stu_class_desc'=>$request->get('stu_class_desc',''),
            ];

          $cin=array_filter($cin);

          if(empty($cin))
          {

            $this->message='请传入要修改的参数！';
            return  $this->format();
          }

          $class=$class->update($cin);


            $this->status=1;
            $this->message='修改班级成功';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }


    /*
        单条
    */
    public function show(){
        die;

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $stu_class_no=$request->get('stu_class_no','');

            $grade = \App\Models\StuClass::where('stu_class_no',$stu_class_no)->first();

            if(empty($grade))
            {
                $this->message='班级不存在！';
                return $this->format();
            }

            $this->status=1;
            $this->message='ok';
            return $this->format($grade);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }


    /*
        删除
    */
    public function del(){
        die;

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

/*            $merchant_id=$loginer->merchant_id;




            $this->status=1;
            $this->message='订单创建成功';
            return $this->format();
*/

        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }






}
