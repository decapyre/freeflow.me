<?php

/* TODO
 *
 * Add tag filtering & date filtering
 *
 */

class ViewController extends \BaseController {
	
	public function showIndex()
	{	
		$posts 		= Post::orderBy('created_at', 'DESC')->paginate( 32 ); // with 365 should produce 5 links + 2 arrows
		$count 		= count($posts);
		$cdn_path 	= $this->_getCDNPath(NULL);

		// meta
		$meta = (object) array(
			'title' 		=> 'Freeflow.me - 1 Art Piece Daily. A Project from Bryan Berger',
			'image_url' 	=> $cdn_path . 'vader_560.jpg' // my fav
		);

		// push cur page to session
		Session::put('curPage', $posts->getCurrentPage());

		// arguments
		$args = array(
			'meta'  	=> $meta,
			'posts' 	=> $posts,
			'count' 	=> $count,
			'cdn_path' 	=> $cdn_path,
			'cart_count'=> Cart::count()
		);

    	return View::make('index', $args);
	}

	public function showDetail($name)
	{	
		$max_days 	= '365';
		//$post = Post::where('filename', $name)->first();

		$posts 		= Post::all();
		$post_index = array_search($name, $posts->lists('filename'), true);

		// if fails to exist
		if($post_index === FALSE || $post_index === NULL)
			return Redirect::to('/');

		// determine post
		$post = $posts->get($post_index);
		$post->sequence_number = $post_index+1;
		
		// remove tags from the object since we exploded them ourselves
		$tags = explode(', ', $post->tags);
		$post->tags = $tags;

		// prev and next objects for simple pageination
		$prev = $posts->get($post_index-1);
		$next = $posts->get($post_index+1);

		$prev4 = [
			$prev,
			$posts->get($post_index-2),
			$posts->get($post_index-3),
			$posts->get($post_index-4)
		];

		// get cdn path
		$cdn_path = $this->_getCDNPath('840/'.$post->filename.'_840');

		// current page
		$curPage  = Session::get('curPage');

		// meta data
		$meta = (object) array(
			'title'     => 'Freeflow.me - 1 Art Piece Daily - ' . $post->name . ' (' .$post->sequence_number . ' of ' . $max_days . ')',
			'image_url' => $cdn_path . $post->filename . '_560.jpg',
			'pageNumUri'=> ($curPage === 1) ? NULL : '?page=' . $curPage
		);

		$args = array(
			'meta' 		=> $meta,
			'post' 		=> $post,
			'prev' 		=> $prev,
			'next' 		=> $next,
			'max_days' 	=> $max_days,
			'cdn_path' 	=> $cdn_path,
			'prev4'		=> $prev4,
			'cart_count'=> Cart::count()
		);

		return View::make('detail', $args);
	}

	private function _getCDNPath($name) {
		//return 'assets/imgs/art/';
		
		if($name === NULL) {
			$name = 'humhum_560'; // humhum is the smallest image filesize wise.
		}

		// check if cached
		if (Cache::has('dropbox_status_code')) {
			$status_code = Cache::get('dropbox_status_code');
		} else {
			// we don't have a cached status_code so grab one and store it for a few hours
			$request = Requests::get('http://dl.dropboxusercontent.com/u/584602/freeflow.me/imgs/art/'.$name.'.jpg');
			$status_code = $request->status_code;
			Cache::put('dropbox_status_code', $status_code, 60);
		}

		// if dropbox has blocked us because of bandwidth limits, use local files
		if($status_code === 509 || $status_code  === 404) {
			$cdn_path = 'http://freeflow.me/assets/imgs/art/';
		} else {
			$cdn_path = 'http://dl.dropboxusercontent.com/u/584602/freeflow.me/imgs/art/';
		}

		return $cdn_path;
	}
}