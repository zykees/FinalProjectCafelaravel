<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery; // สมมติว่าคุณมี Model ชื่อ Gallery

class UserGalleryController extends Controller
{
    public function index()
    {
        // ดึงรูปทั้งหมด (หรือจะ paginate ก็ได้)
        $images = Gallery::orderBy('created_at', 'desc')->get();

        return view('User.gallery.index', compact('images'));
    }
}