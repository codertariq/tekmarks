<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;

class GetUserFromToken {

    protected $jwtAuth;

    public function __construct(JWTAuth $jwtAuth) {
        $this->jwtAuth = $jwtAuth;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next) {

        if (!$token = $this->jwtAuth->setRequest($request)->getToken()) {
            $status = Response::HTTP_BAD_REQUEST;
            return response()->json(['message' => trans('auth.token_not_provided'), 'status' => $status, 'login' => 1], $status);
        }

        try {
            $user = $this->jwtAuth->authenticate($token);
        } catch (TokenExpiredException  $exception) {
            return response()->json(['message' => trans('auth.token_expired'), 'login' => 1], $exception->getStatusCode());
        } catch (TokenInvalidException $exception) {
            return response()->json(['message' => trans('auth.token_invalid'), 'login' => 1], $exception->getStatusCode());
        } catch (TokenBlacklistedException $exception) {
            return response()->json(['message' => trans('auth.token_blacklisted'), 'login' => 1], $exception->getStatusCode());
        } catch (JWTException $exception) {
            return response()->json(['message' => trans('auth.token_unknown'), 'login' => 1], $exception->getStatusCode());
        }

        if (!$user) {
            $status = Response::HTTP_BAD_REQUEST;
            return response()->json(['message' => trans('auth.cannot_fetch_user_from_token'), 'login' => 1, 'status' => $status], $status);

        }
        $response = $next($request);

        return $response;
    }
}
