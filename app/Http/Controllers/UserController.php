<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select('users.*', 'roles.name as role_name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'LIKE', "%{$search}%")
                  ->orWhere('users.email', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->orderBy('users.id', 'desc')->paginate(10)->withQueryString();
        $roles = DB::table('roles')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
        ]);

        $randomPassword = Str::random(8); 

        DB::table('users')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($randomPassword),
            'role_id' => $request->role_id,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            Mail::raw("Xin chào {$request->name},\n\nTài khoản nhân viên của bạn trên hệ thống Smart Grocer đã được tạo thành công.\n\nThông tin đăng nhập:\n- Email: {$request->email}\n- Mật khẩu: {$randomPassword}\n\nVui lòng đăng nhập và đổi lại mật khẩu cá nhân sau khi truy cập hệ thống.", function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('[Smart Grocer] Thông tin tài khoản nhân viên mới');
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => true, 
                'message' => 'Đã tạo nhân viên thành công! (Chưa gửi được mail, mật khẩu tự sinh là: ' . $randomPassword . ')'
            ]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Thêm nhân viên thành công! Mật khẩu ngẫu nhiên đã được gửi về email nhân viên.'
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
        ]);

        DB::table('users')->where('id', $id)->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Cập nhật thông tin nhân viên thành công!']);
    }

    // Hàm cấp lại Mật khẩu ngẫu nhiên & gửi Mail
    public function resetPassword($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy nhân viên!'], 404);
        }

        $newPassword = Str::random(8);

        DB::table('users')->where('id', $id)->update([
            'password' => Hash::make($newPassword),
            'updated_at' => now(),
        ]);

        try {
            Mail::raw("Xin chào {$user->name},\n\nMật khẩu tài khoản nhân viên của bạn trên hệ thống Smart Grocer vừa được cấp lại.\n\nThông tin đăng nhập mới:\n- Email: {$user->email}\n- Mật khẩu mới: {$newPassword}\n\nVui lòng đăng nhập và bảo mật thông tin tài khoản.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('[Smart Grocer] Cấp lại mật khẩu tài khoản nhân viên');
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => true, 
                'message' => 'Cấp lại mật khẩu thành công! (Lỗi gửi mail, mật khẩu mới là: ' . $newPassword . ')'
            ]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Đã cấp lại mật khẩu mới và gửi email tới nhân viên!'
        ]);
    }

    public function toggleStatus($id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy tài khoản!'], 404);
        }

        $newStatus = $user->is_active == 1 ? 0 : 1;
        DB::table('users')->where('id', $id)->update([
            'is_active' => $newStatus,
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Đổi trạng thái tài khoản thành công!']);
    }
}