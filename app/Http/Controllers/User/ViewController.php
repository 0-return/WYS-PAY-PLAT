<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/4
 * Time: 上午11:33
 */
class ViewController extends Controller
{


    public function login()
    {
        return view('user.login');
    }
    public function index()
    {
        return view('user.index');
    }
    public function alipayconfirm()//支付宝配置
    {
        return view('user.alipayconfirm');
    }
    public function wechatconfirm()//微信配置
    {
        return view('user.wechatconfirm');
    }
    
    public function schoollist()//学校列表
    {
        return view('user.schoollist');
    }
    public function agentlist()//代理商列表
    {
        return view('user.agentlist');
    }
    public function waterlist()
    {
        return view('user.waterlist');
    }
    public function qrcode()
    {
        return view('user.qrcode');
    }
    public function seewater()
    {
        return view('user.seewater');
    }
    public function payitem()
    {
        return view('user.payitem');
    }
    public function addagent()
    {
        return view('user.addagent');
    }
    public function examineschool()
    {
        return view('user.examineschool');
    }
    public function editschool()
    {
        return view('user.editschool');
    }
    public function forget()
    {
        return view('user.forget');
    }
    public function storelist()
    {
        return view('user.storelist');
    }
    public function seestore()
    {
        return view('user.seestore');
    }
    public function editstore()
    {
        return view('user.editstore');
    }
    //设备管理
    public function devicelist()
    {
        return view('user.devicelist');
    }
    public function adddevice()
    {
        return view('user.adddevice');
    }
    public function editdevice()
    {
        return view('user.editdevice');
    }
    // 消息    
    public function appmsg()
    {
        return view('user.appmsg');
    }
    public function addappmsg()
    {
        return view('user.addappmsg');
    }
    public function bannerlist()
    {
        return view('user.bannerlist');
    }
    public function addbanner()
    {
        return view('user.addbanner');
    }
    // 角色
    public function role()
    {
        return view('user.role');
    }
    public function addrole()
    {
        return view('user.addrole');
    }
    public function permissions()
    {
        return view('user.permissions');
    }
    public function rolelist()
    {
        return view('user.rolelist');
    }
    // 权限
    public function power()
    {
        return view('user.power');
    }
    public function addpower()
    {
        return view('user.addpower');
    }
    // 交易流水
    public function tradelist()
    {
        return view('user.tradelist');
    }
    // 通道管理
    public function passway()
    {
        return view('user.passway');
    }
    // 通道费率列表
    public function ratelist()
    {
        return view('user.ratelist');
    }
    // 花呗分期流水列表
    public function flowerlist()
    {
        return view('user.flowerlist');
    }
    // 分店管理
    public function branchshop()
    {
        return view('user.branchshop');
    }
    public function addbranchdevice()
    {
        return view('user.addbranchdevice');
    }
    //二维码统一管理
    public function qrcodemanage()
    {
        return view('user.qrcodemanage');
    }
    //广告
    public function ad()
    {
        return view('user.ad');
    }
    public function addad()
    {
        return view('user.addad');
    }
    public function adsee()
    {
        return view('user.adsee');
    }
    public function editad()
    {
        return view('user.editad');
    }
    //赏金
    public function reward()
    {
        return view('user.reward');
    }
    //提现
    public function putforward()
    {
        return view('user.putforward');
    }
    //支付宝红包
    public function alipayred()
    {
        return view('user.alipayred');
    }
    public function addalipayred()
    {
        return view('user.addalipayred');
    }
    //收银插件
    public function shouyin()
    {
        return view('user.shouyin');
    }
    public function addshouyin()
    {
        return view('user.addshouyin');
    }
    //京东配置
    public function jdconfigure()
    {
        return view('user.jdconfigure');
    }
    public function openpassway()
    {
        return view('user.openpassway');
    }
    public function newworld()
    {
        return view('user.newworld');
    }
    public function settlement()
    {
        return view('user.settlement');
    }
    public function jdwhitebar()
    {
        return view('user.jdwhitebar');
    }
    public function storeratelist()
    {
        return view('user.storeratelist');
    }
    public function updata()
    {
        return view('user.updata');
    }
    public function appconfig()
    {
        return view('user.appconfig');
    }
    public function pushconfig()
    {
        return view('user.pushconfig');
    }
    public function msgconfig()
    {
        return view('user.msgconfig');
    }
    public function hrtconfig()
    {
        return view('user.hrtconfig');
    }
    public function passwaysort()
    {
        return view('user.passwaysort');
    }
    public function storeconfig()
    {
        return view('user.storeconfig');
    }
    public function reconciliation()
    {
        return view('user.reconciliation');
    }
    public function devicemanage()
    {
        return view('user.devicemanage');
    }
    public function merchantnumber()
    {
        return view('user.merchantnumber');
    }
    public function withdrawrecord()
    {
        return view('user.withdrawrecord');
    }
    public function deviceconfig()
    {
        return view('user.deviceconfig');
    }  
    public function bound()
    {
        return view('user.bound');
    }    
    public function unbound()
    {
        return view('user.unbound');
    } 
    public function cashset()
    {
        return view('user.cashset');
    } 
    public function settlerecord()
    {
        return view('user.settlerecord');
    } 
    public function settledetail()
    {
        return view('user.settledetail');
    } 
    public function mqtt()
    {
        return view('user.mqtt');
    } 
    // 银盈通
    public function yytong()
    {
        return view('user.yytong');
    } 

    public function merchantmanage()
    {
        return view('user.merchantmanage');
    } 
    public function addstoretransfer()
    {
        return view('user.addstoretransfer');
    } 
    public function transactionlist()
    {
        return view('user.transactionlist');
    } 
    public function percode()
    {
        return view('user.percode');
    } 
    public function addpercode()
    {
        return view('user.addpercode');
    } 
    public function makemoney()
    {
        return view('user.makemoney');
    } 
    // 押金流水deposit
    public function depositwater()
    {
        return view('user.depositwater');
    }
    public function depositacount()
    {
        return view('user.depositacount');
    } 
    public function cashwithdrawal()
    {
        return view('user.cashwithdrawal');
    }
    public function fuyoumanage()
    {
        return view('user.fuyoumanage');
    }  

}