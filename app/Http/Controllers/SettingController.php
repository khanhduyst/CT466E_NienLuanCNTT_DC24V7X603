<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        // Lấy tất cả dữ liệu từ bảng system_settings dưới dạng mảng ['key' => 'value']
        $settings = DB::table('system_settings')->pluck('value', 'key')->toArray();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Lấy tất cả dữ liệu trừ token
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value ?? '',
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }

        return redirect()->back()->with('success', 'Cập nhật cấu hình hệ thống thành công!');
    }
}