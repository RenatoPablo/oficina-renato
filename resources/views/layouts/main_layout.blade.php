@extends(auth()->user()?->is_admin ? 'layouts.admin' : 'layouts.client')
