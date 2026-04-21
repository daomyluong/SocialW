<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Story3;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class StoryController3 extends Controller {
    public function store(Request $request) {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'media' => 'required|mimes:jpg,jpeg,png,mp4,mov|max:51200',
        ]);

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $extension = strtolower($file->getClientOriginalExtension());
            $type = in_array($extension, ['mp4', 'mov']) ? 'video' : 'image';

            $fileName = time() . '_' . $file->getClientOriginalName();
            File::ensureDirectoryExists(public_path('uploads/stories'));
            $file->move(public_path('uploads/stories'), $fileName);

            Story3::create([
                'user_id' => (int) Auth::id(), 
                'media_url' => 'uploads/stories/' . $fileName,
                'type' => $type
            ]);
            return back()->with('success', 'Đã đăng Story mới!');
        }

        return back()->withErrors(['story_store' => 'Không tìm thấy tệp để đăng story.']);
    }
}