<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Story3;

class StoryController3 extends Controller {
    public function store(Request $request) {
        $request->validate([
            'media' => 'required|mimes:jpg,jpeg,png,mp4,mov|max:51200',
        ]);

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $extension = strtolower($file->getClientOriginalExtension());
            $type = in_array($extension, ['mp4', 'mov']) ? 'video' : 'image';

            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/stories'), $fileName);

            Story3::create([
                'user_id' => 1, 
                'media_url' => 'uploads/stories/' . $fileName,
                'type' => $type
            ]);
            return back()->with('success', 'Đã đăng Story mới!');
        }
    }
}