<?php

namespace App\Http\Middleware;

use App\Auth\Common\AjaxResponse;
use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
            //
    ];

    /**
     * token有效期验证
     * @author zt6768
     */
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('post')) {
            $sessionToken = $request->session()->token();
            $inputToken = $request->input('_token');
            if ($sessionToken != $inputToken) {
                if ($request->ajax()) {
                    return AjaxResponse::isFailure(__("auth.token"));
                }
                return back()->with(["message" => __("auth.token")]);
            }
        }
        return parent::handle($request, $next);
    }
}
