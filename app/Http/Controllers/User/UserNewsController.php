<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\News;

class UserNewsController extends Controller
{
    public function index()
    {
        $news = News::where('status', 'published')
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('user.news.index', compact('news'));
    }

    public function show(News $news)
    {
        return view('user.news.show', compact('news'));
    }
}