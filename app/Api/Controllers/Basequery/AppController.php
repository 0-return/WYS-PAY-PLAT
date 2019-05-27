<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/2/6
 * Time: 15:36
 */

namespace App\Api\Controllers\Basequery;


use App\Api\Controllers\BaseController;
use App\Models\App;
use Comodojo\Zip\Zip;
use Comodojo\Zip\ZipManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppController extends BaseController

{
    //检测信息信息
    public function updateInfo(Request $request)
    {
        $app = App::where('id', 1)->first();
        $post_data['type'] = 'versionInfo';
        $post_data['app_version'] = $app->app_version;
        $post_data['app_id'] = $app->app_id;
        $post_data['token'] = $app->token;//邮箱
        $versionInfo = $this->https_request('http://update.umxnt.com/api/xyQuery', $post_data);
        $version = json_decode($versionInfo, true);
        if ($version['status'] == 200) {
            if ($app->app_version < $version['appversionInfo']['original'][0]['version']) {
                return json_encode([
                    'status' => 1,
                    'app_version' => $app->app_version,
                    'app_msg' => $app->msg,
                    'update_status' => 1,
                    'update_version' => $version['appversionInfo']['original'][0]['version'],
                    'update_msg' => $version['appversionInfo']['original'][0]['msg']
                ]);
            } else {
                return json_encode([
                    'status' => 1,
                    'app_version' => $app->app_version,
                    'app_msg' => $app->msg,
                    'update_status' => 0,
                    'update_version' => $version['appversionInfo']['original'][0]['version'],
                    'update_msg' => $version['appversionInfo']['original'][0]['msg']

                ]);
            }
        } else {
            return json_encode([
                'status' => 1,
                'app_version' => $app->app_version,
                'app_msg' => $app->msg,
                'update_status' => 0,
                'update_version' => '暂无更新',
                'update_msg' => '暂无更新'

            ]);
        }

    }

    //更新文件
    public function appUpdateFile(Request $request)
    {
        $app = App::where('id', 1)->first();
        $post_data['type'] = 'versionFile';
        $post_data['app_version'] = $app->app_version;//;
        $post_data['app_id'] = $app->app_id;
        $post_data['token'] = $app->token;//邮箱
        $versionInfo = $this->https_request('http://update.umxnt.com/api/xyQuery', $post_data);
        $data = json_decode($versionInfo, true);
        if ($data['status'] == 200) {
            $fileTemp = public_path() . '/fileTemp/';
            !is_dir($fileTemp) && @mkdir($fileTemp, 0777, true);
            $file = $fileTemp . 'update.zip';
            $sql = base_path() . '/sql.sql';
            @unlink($file);
            @unlink($sql);
            $this->getFile($data['file'], $fileTemp, 'update.zip', 1);//从远程服务器获取文件到本地
            $filesPath = base_path();
            try {
                $zip = Zip::open($file);
                $re = $zip->extract($filesPath);//解压到根目录
            } catch (\Exception $exception) {
                @unlink($file);
                return json_encode([
                    'status' => 1,
                    'message' => '解压文件出错!请检查文件目录权限'
                ]);
            }
            if ($re) {
                @unlink($file);
                //解压成功
                $hassql = file_exists($sql);
                //有数据库文件
                if ($hassql) {
                    ignore_user_abort(true);
                    set_time_limit(0);
                    $file = $sql;
                    $fp = fopen($file, 'r');

                    $str = '';
                    while (!feof($fp)) {

                        $line = fgets($fp);
                        $str .= $line;
                        // 匹配到分號表示一條語句結束
                        $pattern = '/\;/';
                        $result = preg_match($pattern, $line);
                        // 執行語句
                        if ($result) {
                            try {
                                $re = DB::statement($str);
                            } catch (\Exception $exception) {
                                // file_put_contents('./xxxx.txt', $exception->getFile() . $exception->getLine() . $exception->getMessage());
                                continue;
                            }
                            $str = '';
                        }

                    }
                    fclose($fp);
                    @unlink($sql);


                    /*                    $sqlconten = file_get_contents($sql);
                                        try {
                                            $re = DB::statement($sqlconten);
                                        } catch (\Exception $exception) {
                                            @unlink($sql);
                                        }

                    */


                }

                //更新成功 修改数据库
                $post_data['type'] = 'versionInfo';
                $versionInfo = $this->https_request('http://update.umxnt.com/api/xyQuery', $post_data);
                $version = json_decode($versionInfo, true);

                try {
                    $apps = App::where('id', 1)->update(
                        ['app_version' => $version['appversionInfo']['original'][0]['version'],
                            'msg' => $version['appversionInfo']['original'][0]['msg']
                        ]);
                } catch (\Exception $exception) {
                    return json_encode([
                        'status' => 2,
                        'message' => $exception->getMessage()
                    ]);
                }
                return json_encode([
                    'status' => 1,
                    'message' => '更新成功！'
                ]);
            }
        } else {
            return $data;
        }
    }

    //设置参数
    public function setApp()
    {
        $auth = Auth::user()->can('setApp');
        if (!$auth) {
            echo '你没有权限操作！';
            die;
        }
        $app = App::where('id', 1)->first();
        return view('app.set', compact('app'));

    }

    public function setAppPost(Request $request)
    {
        $re = App::where('id', 1)->update($request->except(['_token']));
        if ($re) {
            $json = [
                'status' => 1,
            ];
        } else {
            $json = [
                'status' => 1,
            ];
        }

        return json_encode($json);
    }


    function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_REFERER, 'http://' . $_SERVER["SERVER_NAME"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    private function httpcopy($url, $file = "", $timeout = 60)
    {
        $file = empty($file) ? pathinfo($url, PATHINFO_BASENAME) : $file;
        $dir = pathinfo($file, PATHINFO_DIRNAME);
        !is_dir($dir) && @mkdir($dir, 0755, true);
        $url = str_replace(" ", "%20", $url);
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $temp = curl_exec($ch);
            $status = curl_getinfo($ch);
            if (file_exists($file))
                unlink($file);
            if (@file_put_contents($file, $temp) && !curl_error($ch)) {
                return $status;
            } else {
                return false;
            }
        } else {
            $opts = array(
                "http" => array(
                    "method" => "GET",
                    "header" => "",
                    "timeout" => $timeout)
            );
            $context = stream_context_create($opts);
            if (@copy($url, $file, $context)) {
                return $file;
            } else {
                return false;
            }
        }
    }

    protected function getFile($url, $save_dir = '', $filename = '', $type = 0)
    {
        if (trim($url) == '') {
            return false;
        }
        if (trim($save_dir) == '') {
            $save_dir = './';
        }
        if (0 !== strrpos($save_dir, '/')) {
            $save_dir .= '/';
        }
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return false;
        }
        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 500;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
        }
        //echo $content;
        $size = strlen($content);
        //文件大小
        $fp2 = @fopen($save_dir . $filename, 'a');
        fwrite($fp2, $content);
        fclose($fp2);
        unset($content, $url);
        return array(
            'file_name' => $filename,
            'save_path' => $save_dir . $filename,
            'file_size' => $size
        );
    }
}
