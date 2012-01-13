<?php

class Bundle_Controller extends Controller {

	/**
	 * Tell Laravel we want this class restful. See:
	 * http://laravel.com/docs/start/controllers#restful
	 *
	 * @param bool
	 */
	public $restful = true;

	/**
	 * Array of categories. Used in the forms.
	 *
	 * @param array
	 */
	protected $categories = array();

	/**
	 * Construct
	 *
	 * Pull out needed items and assign assets.
	 */
	public function __construct()
	{
		Asset::add('jquery-tags', 'js/jquery.tagit.js', array('jquery','jquery-ui'));
		// Get the categories
		$cats = Category::all();
		foreach ($cats as $cat)
		{
			$this->categories[$cat->id] = $cat->title;
		}
	}

	/**
	 * Add a bundle
	 *
	 * Create the add bundle form which will send the posted
	 * data to the post_add method.
	 */
	public function get_add()
	{
		// Get the tags
		// This will not be used here. But on the edit page.
		/*
		$tag_query = Tag::where('tag', 'like', Input::get('term').'%')->get();
		$tags = array();
		foreach ($tag_query as $key => $tag)
		{
			$tags[$key] = $tag->tag;
		}

		return View::make('layouts.default')
			->nest('content', 'bundles.add')
			->with('tags', $tags);
		*/
		return View::make('layouts.default')
			->nest('content', 'bundles.add', array(
				'categories' => $this->categories
			));
	}


	/**
	 * Add a bundle
	 *
	 * This handles the posted data from the get_add method above.
	 *
	 */
	public function post_add()
	{
		Input::flash();
		$rules = array(
			'location'     => 'required|url',
			'title'        => 'required|max:200|unique:bundles',
			'summary'      => 'required',
			'description'  => 'required',
			'website'      => 'url',
			'provider'     => '',
			'category_id'  => 'required'
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->invalid())
		{
			return Redirect::to('bundle/add')->with_errors($validator);
		}

		$title = Input::get('title');
		$uri = Str::slug($title, '_');

		$listing = new Listing;
		$listing->title = $title;
		$listing->summary = Input::get('summary');
		$listing->description = Input::get('description');
		$listing->website = Input::get('website');
		$listing->location = Input::get('location');
		$listing->provider = Input::get('provider', 'github');
		$listing->category_id = Input::get('category_id', 1);
		$listing->user_id = 1; //@todo - Get user id from auth
		$listing->uri = $uri;
		$listing->save();

		// Now save tags
		$tag = new Tag;
		$tag->save_tags($listing->id, Input::get('tags'));

		// Now save dependencies
		if ($dependencies = Input::get('dependencies'))
		{
			foreach ($dependencies as $dependency)
			{
				$bundle = Listing::where('title', '=', $dependency)->first();
				if (is_null($bundle))
				{
					continue;
				}
				DB::table('dependencies')->insert(array('bundle_id' => $listing->id, 'dependency_id' => $bundle->id));
			}
		}

		return Redirect::to('bundle/detail/'.$uri);
	}

	/**
	 * Bundle detail page
	 *
	 * @param string item
	 * @return string view
	 */
	public function get_detail($item = '')
	{
		if ($item == '')
		{
			return Response::error('404');
		}

		$bundle = Listing::find($item);
		return View::make('layouts.default')
			->nest('content', 'bundles.detail', array(
				'bundle' => $bundle
			));
	}
}