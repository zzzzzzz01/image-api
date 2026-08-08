<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageController extends Controller
{
    public function index() { return view('index'); }
    public function uploadPage() { return view('upload'); }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Ro\'yxatdan o\'tdingiz',
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Email yoki parol xato'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Tizimga kirdingiz',
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['success' => true, 'message' => 'Tizimdan chiqdingiz']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
 
    public function upload(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'image' => 'required|file|mimes:png,jpeg,jpg|max:5120'
            ]);

            $file = $request->file('image');
            $hash = hash_file('sha256', $file->getRealPath());

            if (Image::where('file_hash', $hash)->exists()) {
                return response()->json(['success' => false, 'message' => 'Bu rasm avval yuklangan!'], 422);
            }

            $fileName = Str::uuid() . '.webp';
            $path = 'images/' . $fileName;
 
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
 
            $encoded = $image->toWebp(75); 
 
            Storage::disk('public')->put($path, (string) $encoded);
 
            $compressedSize = Storage::disk('public')->size($path); 

            Image::create([
                'user_id' => $user->id,
                'file_name' => $fileName,
                'original_name' => $file->getClientOriginalName(),
                'file_hash' => $hash,
                'file_size' => $compressedSize,  
                'mime_type' => 'image/webp',
                'storage_path' => $path,
                'uploaded_at' => now()
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Rasm muvaffaqiyatli yuklandi!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
 
    public function images(Request $request)
    {
        try {
            $user = $request->user();

            $images = Image::where('user_id', $user->id)
                ->orderBy('uploaded_at', 'desc')
                ->get()
                ->map(function($img) {
                    return [
                        'id' => $img->id,
                        'name' => $img->original_name,
                        'size' => $this->formatSize($img->file_size),
                        'date' => $img->uploaded_at->format('d.m.Y H:i'), 
                        'url' => asset('storage/' . $img->storage_path)
                    ];
                });

            return response()->json(['success' => true, 'images' => $images]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $user = $request->user();

            $image = Image::where('id', $id)->where('user_id', $user->id)->first();
            if (!$image) {
                return response()->json(['success' => false, 'message' => 'Rasm topilmadi'], 404);
            }

            Storage::disk('public')->delete($image->storage_path);
            $image->delete();

            return response()->json(['success' => true, 'message' => 'Rasm o\'chirildi']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function formatSize($bytes)
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1024, 2) . ' KB';
    }
}