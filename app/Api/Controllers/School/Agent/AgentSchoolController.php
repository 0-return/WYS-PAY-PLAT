<?php

namespace App\Api\Controllers\School\Agent;
use Illuminate\Support\Facades\DB;

/*
    代理商审核  添加学校
*/
class AgentSchoolController extends \App\Api\Controllers\BaseController
{



    /*
        显示一条学校
    */
    public function show(){
        try{

            $request=app('request');
            $this->status=2;

            $store_id =  $request->get('store_id');
            $school = \App\Models\StuStore::where('store_id',$store_id)->first();
            if(empty($school))
            {
                $this->message='学校资料不存在！';
                return $this->format();
            }

            $this->status=1;
            return $this->format($school);
        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage();
            return $this->format();
        }
    }

    public function check(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;


            $store_id=$request->get('store_id');
            $status=$request->get('status');
            $status_desc=$request->get('status_desc','');


            if(empty($status)){

                $this->message='审核状态不能为空！';
                return $this->format();
            }



            $obj = \App\Models\StuStore::where('store_id',$store_id)->first();

            if(empty($obj)){

                $this->message='您要审核的学校不存在！';
                return $this->format();
            }

            if($obj->status != 2){                
                $this->message='您要审核的学校状态为非审核状态！';
                return $this->format();
            }

            if(!in_array($status, [1,3]))
            {     
                $this->message='审核状态不正确！';
                return $this->format();
            }


            if($status==1)
            {
                $obj->status_desc= empty($status_desc) ? '审核成功' : $status_desc;

            }else{
                $obj->status_desc= empty($status_desc) ? '审核失败' : $status_desc;
                
            }


            $obj->status=$status;
            // $obj->status_desc=$status_desc;
            $obj->update();

            $this->status=1;
            $this->message='操作成功！';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage();
            return $this->format();
        }

    }




    /*
        学校资料同步到支付宝
    */
    public function sync(){
        try{

            $request=app('request');
            $this->status=2;

            $store_id =  $request->get('store_id');
            $school = \App\Models\StuStore::where('store_id',$store_id)->first();
            if(empty($school))
            {
                $this->message='学校资料不存在！';
                return $this->format();
            }
            if($school->alipay_status ==1)
            {
                $this->message='学校资料已经同步过了，不能再次同步！';
                return $this->format();
            }

            if($school->status !=1)
            {
                $this->message='请等待系统审核后同步！';
                return $this->format();
            }

            if(!in_array($school->school_type, [1,2,3,4,5]))
            {
                $this->message='学校类型不支持！';
                return $this->format();
            }



            $pay_notify_url=url('api/school/pay/notify');//支付结果异步通知地址


// 配置检测--------start
            $ali_config = \App\Models\AlipayIsvConfig::where('config_id',$school->config_id)->first();

            //pid
            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

            if($check !== true)
            {
                $this->message=$check;
                return $this->format();
            }



/*
            // app_name  app_phone
            $ali_config_msg = \App\Models\AppConfigMsg::where('config_id',$school->config_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfigmsg($ali_config_msg);

            if($check !== true)
            {
                $this->message=$check;
                return $this->format();
            }


*/


            //ali_user_id
            $ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id',$school->store_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolauthuser($ali_app_auth_user);

            if($check !== true)
            {
                $this->message=$check;
                return $this->format();
            }

// 配置检测--------end


            $img_type=substr($school->school_icon, strrpos($school->school_icon, '.')+1);
            $send_ali_data=[
                    'school_name'=>$school->school_name,
                    // 'school_icon'=>$school->school_icon,
                    // 'school_icon_type'=>$img_type,
                    'school_stdcode'=>$school->school_stdcode,
                    'school_type'=>implode(',', str_split($school->school_type)),
                    'province_code'=>$school->province_code,
                    'province_name'=>$school->province_name,
                    'city_code'=>$school->city_code,
                    'city_name'=>$school->city_name,
                    'district_code'=>$school->district_code,
                    'district_name'=>$school->district_name,

                    'isv_name'=>$ali_config->isv_name,

                    'isv_notify_url'=>$pay_notify_url,
                    'isv_pid'=>$ali_config->alipay_pid,

                    'isv_phone'=>$ali_config->isv_phone,
                    'school_pid'=>$ali_app_auth_user->alipay_user_id,
            ];

            $send_ali_data= array_filter($send_ali_data);


            $api_return=\App\Logic\PrimarySchool\SchoolInfo::sync($ali_config,$send_ali_data,$ali_app_auth_user);

            if($api_return['status']==1){

                if(!empty($api_return['school_no'])){
                    $school->school_no=$api_return['school_no'];
                }

                $school->alipay_status=1;
                $school->alipay_status_desc='支付宝审核通过';
                $school->update();

                $this->status=1;
                $this->message='同步成功！'.$api_return['message'];
                return $this->format();

            }else{

                $school->alipay_status=3;
                $school->alipay_status_desc='支付宝审核失败：'.$api_return['message'];
                $school->update();


                $this->message='同步失败！'.$api_return['message'];
                return $this->format();

            }
        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage();
            return $this->format();
        }
    }








/*
    根据store_id  修改学校信息
*/
    public function save(){



        try{

            $pay_notify_url=url('api/school/pay/notify');//支付结果异步通知地址
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;



            $store_id=$request->get('store_id');
            if(empty($store_id))
            {
                $this->message='请传入要修改的store_id！';
                return $this->format();
            }


            $school = \App\Models\StuStore::where('store_id',$store_id)->first();
            if(empty($school))
            {
                $this->message='您要修改的学校不存在！';
                return $this->format();
            }

            if($school->alipay_status==1){
                $this->message='当前学校状态已不能修改！';
                return $this->format();
            }




            // 数据库字段
            $cin=[
                // 'store_id'=>$choose_store_id,
// 'user_id'=>$loginer->user_id,
                // 'merchant_id'=>$merchant_id,
                // 'config_id'=>$loginer->config_id,
                // 'pid'=>!empty($request->get('parent_store_id')) ? $request->get('parent_store_id') : 0,//上级id
                // 'school_no'=>'',

                'school_name'=>$request->get('school_name'),
                'school_sort_name'=>$request->get('school_sort_name'),
                'school_icon'=>$request->get('school_icon'),//图片宽度 高度  必须是  108*108  不大于20kb
                'school_stdcode'=>empty($request->get('school_stdcode')) ? '' : $request->get('school_stdcode'),
                'school_type'=>$request->get('school_type'),
                'province_code'=>$request->get('province_code'),
                'province_name'=>$request->get('province_name'),
                'city_code'=>$request->get('city_code'),
                'city_name'=>$request->get('city_name'),
                'district_code'=>$request->get('district_code'),
                'district_name'=>$request->get('district_name'),
                'su_store_address'=>$request->get('su_store_address'),

                // 'status'=>'2',
                // 'status_desc'=>'未审核',//未审核

                // 'alipay_status'=>2,
                // 'alipay_status_desc'=>'未同步'
            ];

              $validate=\Validator::make($cin, [
                'su_store_address'=>'required',
                        'school_name'=>'required',
                        'school_sort_name'=>'required',
                        'school_icon'=>'required',
                        // 'school_stdcode'=>'required',
                        'school_type'=>'required',
                        'province_code'=>'required',
                        'province_name'=>'required',
                        'city_code'=>'required',
                        'city_name'=>'required',
                        'district_code'=>'required',
                        'district_name'=>'required',
                    // 'cate_id'=>'required|exists:goods_cate,id',
              ], [
                  'required' => ':attribute为必填项！',
                  'min' => ':attribute长度不符合要求！',
                  'max' => ':attribute长度不符合要求！',
                  'unique' => ':attribute已经被人占用！',
                  'exists' => ':attribute不存在！'
              ], [
              'su_store_address'=>'学校详细地址',
                        'school_name'=>'学校名称',
                        'school_sort_name'=>'学校简称',
                        'school_icon'=>'学校图标',
                        'school_stdcode'=>'学校机构编号',
                        'school_type'=>'学校类型',
                        'province_code'=>'省编码',
                        'province_name'=>'省名称',
                        'city_code'=>'市编码',
                        'city_name'=>'市名称',
                        'district_code'=>'区编码',
                        'district_name'=>'区名称',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }

            $ok = $school->update($cin);

            if($ok)
            {
                $this->status=1;
                $this->message='修改成功！';
                return $this->format();
                
            }else{
                $this->status=2;
                $this->message='修改失败，请重试！';
                return $this->format();
            }




        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage();
            return $this->format();
        }




    }






/*
    根据store_id  修改学校信息
*/
    public function save_bak(){


        try{

            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $store_id=$request->get('store_id');
            if(empty($store_id))
            {
                $this->message='请传入要修改的store_id！';
                return $this->format();
            }

            $school = \App\Models\StuStore::where('store_id',$store_id)->first();
            if(empty($school))
            {
                $this->message='您要修改的学校不存在！';
                return $this->format();
            }

            if($school->alipay_status==1){

                $this->message='当前学校状态已不能修改！';
                return $this->format();
            }

            //只能修改   status  school_short_name      异步通知地址只能邮件去修改
            $cin=[];

            if(!empty($request->get('school_name')))
            {
                $cin['school_name'] = $request->get('school_name');
            }

            if(!empty($request->get('school_sort_name')))
            {
                $cin['school_sort_name'] = $request->get('school_sort_name');
            }

            if(!empty($request->get('school_icon')))
            {
                $cin['school_icon'] = $request->get('school_icon');

/*                  $logo = $this->logoCheck($cin['school_icon']);

                  if($logo['status'] !=1)
                  {
                    $this->message=$logo['message'];
                    return $this->format();
                  }
*/
            }

            if(!empty($request->get('school_stdcode')))
            {
                $cin['school_stdcode'] = $request->get('school_stdcode');
            }

            if(!empty($request->get('school_type')))
            {
                $cin['school_type'] = $request->get('school_type');
            }

            if(!empty($request->get('province_code')))
            {
                $cin['province_code'] = $request->get('province_code');
            }

            if(!empty($request->get('province_name')))
            {
                $cin['province_name'] = $request->get('province_name');
            }
 
            if(!empty($request->get('city_code')))
            {
                $cin['city_code'] = $request->get('city_code');
            }
 
            if(!empty($request->get('su_store_address')))
            {
                $cin['su_store_address'] = $request->get('su_store_address');
            }

            if(!empty($request->get('city_name')))
            {
                $cin['city_name'] = $request->get('city_name');
            }

            if(!empty($request->get('district_code')))
            {
                $cin['district_code'] = $request->get('district_code');
            }

            if(!empty($request->get('district_name')))
            {
                $cin['district_name'] = $request->get('district_name');
            }

            if(empty($cin))
            {
                    $this->message='请传入要修改的参数！';
                    return $this->format();
            }

            $ok = $school->update($cin);



            $this->status=1;
            $this->message='修改成功！';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage();
            return $this->format();
        }




    }









}
