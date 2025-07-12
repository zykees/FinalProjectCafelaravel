<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Gallery;
use App\Models\Contact;

class PageController extends Controller
{
    public function about()
    {
        return view('User.pages.about');
    }

    public function contact()
    {
        return view('User.pages.contact');
    }

    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        Contact::create($validated);

        return back()->with('success', 'ส่งข้อความสำเร็จ เราจะติดต่อกลับโดยเร็วที่สุด');
    }

    public function privacy()
    {
        return view('User.pages.privacy');
    }

    public function terms()
    {
        return view('User.pages.terms');
    }

    public function faq()
    {
        return view('User.pages.faq');
    }

    public function menu()
    {
        return view('User.pages.menu');
    }

    public function gallery()
    {
        $galleries = Gallery::latest()->paginate(12);
        return view('User.pages.gallery', compact('galleries'));
    }

    public function news()
    {
        $news = News::latest()->paginate(9);
        return view('User.pages.news.index', compact('news'));
    }

    public function showNews(News $news)
    {
        return view('User.pages.news.show', compact('news'));
    }
}