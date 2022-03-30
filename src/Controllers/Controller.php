<?php

namespace Jxgame\Aibisai\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 成功后提示
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function ok($message, $code = 200)
    {
        if ($message instanceof LengthAwarePaginator) {
            return response()->json(['code' => (int)$code, 'message' => $message->toArray()]);
        }
        return response()->json(['code' => (int)$code, 'message' => $message]);
    }

    /**
     * 失败后提示
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($message, $code = 101)
    {
        return response()->json(['code' => $code, 'message' => $message]);
    }

    /**
     * 分页
     */
    public function perpage($max = 200)
    {
        $pageSize = app('request')->input('pageSize');
        if (empty($pageSize)) {
            $pageSize = 10;
        }
        $perpage = (int)$pageSize;
        return $perpage > $max ? $max : $perpage;
    }

    public function createToken($user, $flag = 'app')
    {

        $flag = app('request')->input('platform') ?? $flag;
       
        $customClaims = ['sub' => $user->id, 'sign' => $flag];
        $token = \JWTAuth::customClaims($customClaims)->fromUser($user);
        $result = [
            'token' => $token,
            'expired_at' => \Carbon\Carbon::now()->addMinutes(config('jwt.ttl'))->toDateTimeString(),
            'refresh_expired_at' => \Carbon\Carbon::now()->addMinutes(config('jwt.refresh_ttl'))->toDateTimeString(),
            'user' => $user
        ];
        $cache = Cache::forever('Token_' . $flag . $user->id, $token);
        return $result;
    }

    /**
     * 权限检查
     */

    public function hasPermission($name)
    {
        //return true;
        $user = auth('admin')->user();
        if ($user->state === 0) return false;
        if ($user->id === 1) return true;//开发
        $permission = cacheMan('rolePermission_' . $user->role_id);
        if ($permission) {
            if (\in_array($name, $permission)) return ['id' => $user->id, 'role_id' => $user->role_id, 'user_name' => $user->user_name, 'real_name' => $user->real_name];
        }
        return false;
    }

    /**
     * 获取当前用户
     * @return mixed
     */
    public function authUser()
    {
        $token = \JWTAuth::getToken();
        if (!$token) return false;
        $payload='';
        try {
            $payload = \JWTAuth::decode($token);
        } catch (\Exception $exception) {
            $this->JSON('Token解析失败', 401); 
        } 
        $sign =$payload && $payload->get('sign') ? $payload->get('sign') : ''; 

        if(!in_array($sign,['app','admin','wx_mini'])) $this->JSON('无效的授权：'.$sign, 401); 

         $user = auth($sign)->user()  ?? '';
         $userid = $user->id  ?? 0;


        if (empty($sign) || empty($user) ) $this->JSON(['code' => 401, 'message' => "登录已失效"]);

        //检查缓存中的
        $cache = Cache::get('Token_' . $sign . $userid);
        if (!$cache || $token != $cache) $this->JSON(['code' => 401, 'message' => "登录已过期"]);

        if ($sign == 'app') {
            if ($user && $user->is_banned === 1) {
                Cache::forget('Token_' . $sign . $user->id);
                return $this->error('用户被禁用', 401);
            }

            if ($user && $user->is_freeze === 1) {
                Cache::forget('Token_' . $sign . $user->id);
                return $this->error('用户被冻结', 401);
            }
        }
        $user->real_id = $user->parent_id>0?$user->parent_id:$user->id;
        return $user;
    }

    public function removeToken($user, $flag = 'app')
    {
        Cache::forget('Token_' . $flag . $user->id);
    }

    public function JSON($message, $code = 401) {
        header("access-control-allow-headers: Accept,Authorization,Cache-Control,Content-Type,DNT,If-Modified-Since,Keep-Alive,Origin,User-Agent,X-Mx-ReqToken,X-Requested-With");
        header("access-control-allow-methods: GET, POST, PUT, DELETE, HEAD, OPTIONS");
        header("access-control-allow-credentials: true");
        header("access-control-allow-origin: *");
        header('X-Powered-By: WAF/2.0');
        $res = \is_array($message) ? $message : ['code' => $code, 'message' => $message];
        exit(json_encode($res));
    }

}
