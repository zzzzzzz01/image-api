<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try { 
            $userId = $request->header('X-User-ID');
             
            if (!$userId) {
                $userId = session('user_id');
            }
            
            Log::info('Auth Middleware', [
                'header_user_id' => $request->header('X-User-ID'),
                'session_user_id' => session('user_id'),
                'final_user_id' => $userId
            ]);
             
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Avval tizimga kiring'
                ], 401);
            }
 
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Foydalanuvchi topilmadi'
                ], 401);
            }
 
            $request->merge(['user_id' => $userId]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
 
            if (!session('user_id')) {
                session(['user_id' => $user->id]);
                session(['user_name' => $user->name]);
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Auth middleware xatolik: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Auth xatoligi: ' . $e->getMessage()
            ], 500);
        }
    }
}