<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Models\User;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::query();

        if($search)
        {
            $users->where(function($query) use ($search)
            {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $users = $users->orderByDesc('created_at')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));

    }

    public function create()
    {
        
    }
}
