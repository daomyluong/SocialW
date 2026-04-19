<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\User; 

class ProfileController extends Controller
{
    /**
     * Hiển thị trang cá nhân (Show)
     */
    public function show()
    {
        // Lấy thông tin user đang đăng nhập
        $user = Auth::user(); 

        if (!$user) {
            return redirect()->route('login');
        }

        return view('profile.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa (Edit)
     */
    public function edit()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        return view('profile.edit', compact('user'));
    }

    /**
     * Xử lý cập nhật thông tin (Update)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Kiểm tra dữ liệu (Validation) bao gồm cả avatar và bio
        $request->validate([
            'display_name' => 'required|string|max:100',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 1. Xử lý upload ảnh đại diện (nếu có chọn file mới)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/avatars'), $fileName);
            $user->avatar = $fileName; 
        }

        // 2. Cập nhật các thông tin văn bản từ form
        $user->display_name = $request->display_name;
        $user->bio = $request->bio; // Thêm dòng này để lưu tiểu sử vào DB

        // 3. Lưu vào Database
        $user->save();

        // Chuyển hướng về trang profile kèm thông báo thành công
        return redirect()->route('profile.show')->with('success', 'Cập nhật thành công!');
    }
}