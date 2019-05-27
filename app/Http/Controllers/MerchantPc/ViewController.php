<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/9
 * Time: 下午4:03
 */

namespace App\Http\Controllers\MerchantPc;


use App\Http\Controllers\Controller;

class ViewController extends Controller
{
    public function home()
    {

        return view('merchantpc.home');
    }

    public function login()
    {

        return view('merchantpc.login');
    }
    public function forget()
    {

        return view('merchantpc.forget');
    }
    public function index()
    {

        return view('merchantpc.index');
    }
    public function schoollist()
    {

        return view('merchantpc.schoollist');
    }
    public function addschool()
    {

        return view('merchantpc.addschool');
    }
    public function editschool()
    {

        return view('merchantpc.editschool');
    }
    public function examineschool()
    {

        return view('merchantpc.examineschool');
    }
    //年级
    public function gradelist()
    {

        return view('merchantpc.gradelist');
    }
    public function addgrade()
    {

        return view('merchantpc.addgrade');
    }
    public function editgrade()
    {

        return view('merchantpc.editgrade');
    }
    //班级
    public function classlist()
    {

        return view('merchantpc.classlist');
    }
    public function addclass()
    {

        return view('merchantpc.addclass');
    }
    public function editclass()
    {

        return view('merchantpc.editclass');
    }
    public function assignteacher()//班级教师列表
    {

        return view('merchantpc.assignteacher');
    }
    public function assigntercalss()/*分配教师*/
    {

        return view('merchantpc.assigntercalss');
    }
    // 学生
    public function studentlist()
    {

        return view('merchantpc.studentlist');
    }
    public function addstudent()
    {

        return view('merchantpc.addstudent');
    }
    public function editstudent()
    {

        return view('merchantpc.editstudent');
    }
    public function paystudent()//学生缴费记录
    {

        return view('merchantpc.paystudent');
    }
    // 教师
    public function teacherlist()
    {

        return view('merchantpc.teacherlist');
    }
    public function addteacher()
    {

        return view('merchantpc.addteacher');
    }
    public function editteacher()
    {

        return view('merchantpc.editteacher');
    }
    // 模板名称
    public function paymanagelist()
    {

        return view('merchantpc.paymanagelist');
    }
    public function addpaytemplate()
    {

        return view('merchantpc.addpaytemplate');
    }
    public function seetemplate()
    {

        return view('merchantpc.seetemplate');
    }
    public function edittemplate()
    {

        return view('merchantpc.edittemplate');
    }
    public function examinetemplate()
    {

        return view('merchantpc.examinetemplate');
    }
    // 缴费项目
    public function paymentlist()//列表
    {

        return view('merchantpc.paymentlist');
    }
    public function paymentitem()//添加缴费模板
    {

        return view('merchantpc.paymentitem');
    }
    public function examinepayment()//审核
    {

        return view('merchantpc.examinepayment');
    }
    public function seepayment()//查看
    {

        return view('merchantpc.seepayment');
    }
    public function paydetail()//点击学校查看模板
    {

        return view('merchantpc.paydetail');
    }
    public function editpayment()//修改缴费项目
    {

        return view('merchantpc.editpayment');
    }
    
    // 缴费记录管理
    public function payrecord()//缴费记录管理列表
    {

        return view('merchantpc.payrecord');
    }
    public function seepayrecord()//查看缴费记录管理
    {

        return view('merchantpc.seepayrecord');
    }


    //缴费情况统计
    public function paycount()//缴费情况统计列表
    {

        return view('merchantpc.paycount');
    }
    //当面付
    public function facepay()
    {

        return view('merchantpc.facepay');
    }
    //缴费记录管理--缴费小项
    public function minoritem()
    {

        return view('merchantpc.minoritem');
    }
    //班级列表导入学生资料
    public function exportstudata()
    {

        return view('merchantpc.exportstudata');
    }
    //导入缴费账单
    public function importbill()
    {

        return view('merchantpc.importbill');
    }
    //导入缴费账单
    public function alipayauth()
    {

        return view('merchantpc.alipayauth');
    }
    //分校管理
    public function branchschool()
    {

        return view('merchantpc.branchschool');
    }
    //添加分校
    public function addbranchsch()
    {

        return view('merchantpc.addbranchsch');
    }
    

    
}