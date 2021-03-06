<?php

/*
 * This file is part of ibrand/wechat-backend.
 *
 * (c) iBrand <https://www.ibrand.cc>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  iBrand\Wechat\Backend\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\MessageBag;
use Response;
use Session;

class AccountRequestMiddleware
{
    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->is('admin/wechat/*')) {
            if (empty(wechat_platform()->getToken())) {

                $error = new MessageBag([
                    'title'   => '请配置第三方平台',
                    'message' => '使用微信管理功能，需要先配置好第三方平台',
                ]);

                return back()->with(compact('error'));
            }

            if (!Session::has('account_app_id') || !Session::has('account_id')) {
                return redirect()->route('admin.wechat.account.index');
            }
        }

        if ($request->is('admin/wechat-api/*')) {
            if (empty(wechat_platform()->getToken())) {
                return response()->json(
                    ['status' => false, 'code' => 400, 'message' => '未授权验证', 'data' => ['url' => route('admin.wechat.init')]]);
            }

            if (empty(wechat_platform()->getMainAccount())) {
                return response()->json(
                    ['status' => false, 'code' => 400, 'message' => '未设置主公众号', 'data' => ['url' => route('admin.wechat.account.index')]]);
            }
        }

        return $next($request);
    }
}
