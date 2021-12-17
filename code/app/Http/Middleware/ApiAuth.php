<?php
    /**
     * Created by yuwei.
     * User: zt8067
     * Date: 2019/6/17
     * Time: 15:32
     */

    namespace App\Http\Middleware;
    use Closure;
    class ApiAuth
    {
        public function handle($request, Closure $next)
        {
            if ($request->isMethod('post')) {
                $envToken = config('api.token');
                $urlToken = $request->input('token');
                if ($envToken != $urlToken) {
                    return response()->json(['code'=>-1,'msg'=>'非法访问'],401);
                }
            }
            return $next($request);
        }
    }