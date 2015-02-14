<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function index()
	{
		$themes = Theme::with('art')->with('art.like_users')->with('art.user')->where('date', '<=', \Carbon\Carbon::today())->orderBy('date', 'DESC')->paginate(3);
		$theme = Theme::today();
		if(is_null($theme)){
			$result = Theme::getTopicFromSuggestion();
			if(Auth::user() && !$result){
				$themes = Theme::get();
				return View::make('suggestions.new', compact('themes'));				
			}elseif(!Auth::user())
			{
				$theme = Theme::orderBy('id','desc')->first();
				return View::make('home.index')->with(compact('themes','theme'));
			}
			return Redirect::to('/');
		}
		return View::make('home.index')->with(compact('themes','theme'));
	}

	public function login()
	{
		$arts = Art::orderBy('created_at', 'DESC')->take(50)->get();

		if(Auth::check()) {
			return Redirect::to('user');
		}
		else {
			return View::make('home.login')->with(compact('arts'));
		}
	}

	public function do_login()
	{
		if(Auth::attempt(Input::except('_token'), true))
			return Redirect::to('user');
		else
			return Redirect::back()->withInput()->with('message','Invalid credentials');
	}

	public function logout()
	{
		Auth::logout();
		return Redirect::to('/');
	}

	public function getRegisterUser($token)
	{
		$theme = Theme::today()->theme;
		$user = User::getRegisterUser($token);
		if($user)
		{
			return View::make('home.register',compact('user','theme'));
		}
		else
			return Redirect::to('/');
	}

	public function storeUser()
	{
		$input = Input::all();
		$user = User::storeUser($input);
		if($user)
			return Redirect::to('/');
		else
			return Redirect::back()->with('message','There was some error in registration');
	}
}