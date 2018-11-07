<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/23
 * Time: 14:18
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileUpLoadController extends Controller
{
    /**
     * 上传文件
     * @param Request $request
     * @return array
     */
    public function FileUpLoad(Request $request){
        $uid = $request->input('uid','');
        $key = $request->input('key','');
        $user =  DB::table('pro_mall_users')->where([['uid',$uid],['file_key',$key]])->count();
        //验证用户是uid或key是否错误
        if(empty($user)){
            return ['success'=>'','error'=>'uid 或 key 错误'];
        }
        $files = $request->allFiles();
        $returnPath = [];
        $error = [];
        foreach ($files as $file){
            if($file -> isValid()){
                //检验一下上传的文件是否有效
                $clientName = $file -> getClientOriginalName(); //获取文件名称
                $ext = $file -> getClientOriginalExtension();  //上传文件的后缀
                $size = $file->getSize();
                if($size >= 2*1024*1024){
                    $error[] =  '文件：['.$clientName.']上传失败，文件大于2M';
                }else{
                    $dir = $uid.'/'.date('Y-m-d').'/';
                    $newName = md5($clientName.rand(0,999)).'.'.$ext;
                    $file -> move('files/'.$dir,$newName);
                    $returnPath[] = env('FILE_HTTP_URL').$dir.$newName;
                }
            }
        }
        return ['success'=>$returnPath,'error'=>$error];
    }

    /**
     * 删除文件
     * @param Request $request
     * @return array
     */
    public function  DeleteFile(Request $request){
        try{
            $uid = $request->input('uid','');
            $key = $request->input('key','');
            $urls = $request->input('urls','');
            $user =  DB::table('pro_mall_users')->where([['uid',$uid],['file_key',$key]])->count();
            //验证用户是uid或key是否错误
            if(empty($user)){
                return ['error'=>'uid 或 key 错误'];
            }
            if(empty($urls)){
                return ['error'=>'文件地址不能为空'];
            }
            $url_arr = explode('|',$urls);
            $error = [];
            foreach($url_arr as $url){
                $path = 'files'.parse_url($url,PHP_URL_PATH);
                if(unlink($path)){

                }else{
                    $error[] = $url.' [删除失败]';
                }
            }
            if(empty($error)){
                return ['success'=>'ok'];
            }else{
                return ['error'=>$error];
            }

        }catch (\Exception $e){
            return ['error'=>$e->getMessage()];
        }

    }

}