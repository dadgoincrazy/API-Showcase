<?php
class api {
	private $url;
	
	public function __construct( string $url )
	{
		$this->url = $url;
	}
	
	function get_url()
	{
		return $this->url;
	}
	
	function set_url( $url )
	{
		$this->url = $url;
	}
	
	/** GET
	 *  Uses cURL to get the api URL content
	 *  @return GET content from URL
	 * */
	function GET()
	{
		$handle = curl_init();
		
		// Sets the URL
		curl_setopt( $handle, CURLOPT_URL, $this->get_url() );
		
		// Allows returning of contents
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
		
		$res = curl_exec( $handle );
		
		// Checks if the http code returned with a success, might be useful later
		//$http_code = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
		//
		//if( 200 <= $http_code and $http_code < 300 )
		//{
		//	echo 'Success';
		//}
		
		curl_close( $handle );
		
		return $res;
	}
	
	/** POST
	 *  Uses cURL to post data to the api URL
	 *  @param array data : assoc array with fields => values to be posted
	 *  @return result of the post
	 * */
	function POST( array $data = [] )
	{
		$handle = curl_init();
		
		$data = json_encode( $data );
		
		curl_setopt_array( $handle, array(
			// Sets the URL
			CURLOPT_URL => $this->get_url(),
			// Enables Post
			CURLOPT_POST => true,
			// Data to be posted
			CURLOPT_POSTFIELDS => $data,
			// Allows returning of contents
			CURLOPT_RETURNTRANSFER => true,
			// Header for sending json
			CURLOPT_HTTPHEADER => array(
				'Content-type: application/json; charset=UTF-8'
			)
		));
		
		$res = curl_exec( $handle );
		
		curl_close( $handle );
		
		return $res;
	}

	public function __toString()
	{
		return $this->get_url();
	}
}

class api_tester
{
	/********* Test 1 : GET *********/
	function test_get()
	{
		$url = "https://jsonplaceholder.typicode.com/users";
		$api = new api( $url );
		
		$res = $api->GET();
		
		// Assuming the result is JSON
		return( json_decode( $res ) );
	}
	
	/********* Test 2 : POST *********/
	function test_post()
	{
		$url = "https://jsonplaceholder.typicode.com/posts";
		$api = new api( $url );
		
		$data = [
			'userId' => 1,
			'title' => 'Testing Post',
			'body' => 'This is a post to check if my api post method is working'
		];
		
		$res = $api->POST( $data );

		// Assuming the result is JSON
		return( json_decode( $res ) );
	}
	
	/********* Test 3 : GET USER + COMMENTS *********/
	/* No comments correlate to any user, so we don't get anything from this unfortunately */
	function test_get_users_with_comments()
	{
		$out = [];
		
		$base_url = "https://jsonplaceholder.typicode.com";
		$url = "https://jsonplaceholder.typicode.com/users";
		$api = new api( $url );
		
		$users = json_decode( $api->GET() );
		
		// Assigning some variables I plan to use before the loop so I don't constantly assign them
		$ul_class = "user-info-list";
		$li_class = "user-info-list-item";
		
		// Foreach user, we are going to output a title with their name and a list of their data
		// And then under each user we are gonna have a list of all their comments and info about the post
		foreach( $users as $user )
		{
			$out[] = "<div class='user-container'>";
			
			$out[] = "<div class='user-info'>";
			$out[] = "<h2>$user->name</h2>";
			$out[] = "<ul class='$ul_class'>";
			foreach( $user as $key => $value )
			{
				if( $key == "name" )
				{
					continue;
				}
				
				if( gettype( $value ) == "string" )
				{
					$out[] = "<li class='$li_class'>$key : $value</li>";
				}
				
				if( in_array( gettype( $value ), ["object", "array"] ) )
				{
					$out[] = "<li class='$li_class'>$key :</li>";
					$out[] = self::nested_list_to_html( $value );
				}
			}
			$out[] = "</ul>"; // End of user-info-list
			$out[] = "</div>"; // End of user-info
			
			// Users Comments
			$query = ['email' => $user->email];
			$comment_url = $base_url . "/comments?" . http_build_query($query);
			
			$api->set_url( $comment_url );
			$comments = json_decode( $api->GET() );
			
			// Output the comments with a heading of the name of the comment and the body
			$out[] = "<div class='user-comments-container'>";
			
			foreach($comments as $comment)
			{
				$out[] = "<h3>$comment->name</h3>";
				$out[] = "<div class='user-comments-info'>";
				$out[] = self::nested_list_to_html( $comment );
				$out[] = "</div>"; // End of user-comments-info
			}
			
			$out[] = "</div>"; // End of user-comments-container
			
			
			$out[] = "</div>"; // End of user-container
		}
		
		return( implode( $out ) );
	}
	
	/********* Test 4 : GET USER POST INTRO *********
	* Attempts to get all users and post an intro for each one
	**/
	function test_get_users_post_intro()
	{
		$out = [];
		
		$posts_url = "https://jsonplaceholder.typicode.com/posts";
		$users_url = "https://jsonplaceholder.typicode.com/users";
		$api = new api( $users_url );
		
		$users = json_decode( $api->GET() );
		
		// Assigning some variables I plan to use before the loop so I don't constantly assign them
		$ul_class = "user-info-list";
		$li_class = "user-info-list-item";
		
		// Foreach user, we are going to output a title with their name and a list of their data
		// And then under each user we will put a message showing an intro we posted as the user
		foreach( $users as $user )
		{
			$out[] = "<div class='user-container'>";
			
			$out[] = "<div class='user-info'>";
			$out[] = "<h2>$user->name</h2>";
			$out[] = "<ul class='$ul_class'>";
			foreach( $user as $key => $value )
			{
				if( $key == "name" )
				{
					continue;
				}
				
				if( gettype( $value ) == "string" )
				{
					$out[] = "<li class='$li_class'>$key : $value</li>";
				}
				
				if( in_array( gettype( $value ), ["object", "array"] ) )
				{
					$out[] = "<li class='$li_class'>$key :</li>";
					$out[] = self::nested_list_to_html( $value );
				}
			}
			$out[] = "</ul>"; // End of user-info-list
			$out[] = "</div>"; // End of user-info
			
			// Post Introductions as each user
			$data = [];
			$title = "Introducing the amazing $user->name !";
			$body = "Hello everyone I am the wonderful $user->name,
			you can reach me at my email : $user->email.
			Thank you for your time!";
			$data["userId"] = $user->id;
			$data["title"] = $title;
			$data["body"] = $body;
			
			$api->set_url( $posts_url );
			$res = json_decode( $api->POST( $data ) );
			
			// Output result of the post for each user
			$out[] = "<div class='user-posts-container'>";
			$out[] = "<h3>$user->name's introduction post</h3>";
			$out[] = self::nested_list_to_html( $res );
			
			$out[] = "</div>"; // End of user-posts-container
			
			$out[] = "</div>"; // End of user-container
		}
		
		return( implode( $out ) );
	}
	
	/**
	 * Takes an object or array and turns it into nested lists
	 * Makes a new li for each string found and a new ul for each object / array
	 * Currently ignores ints, so it automatically exludes ids for the time being
	 *
	 * @return string HTML list representation of nested arrays / objects
	 * */
	private function nested_list_to_html( $iter , $ul_class = 'info-list', $li_class = 'info-list-item' )
	{
		$out = [];
		
		if( gettype( $iter ) == 'object' )
		{
			$iter = ( array ) $iter;
		}
		
		$out[] = "<ul class='$ul_class'>";
		foreach( $iter as $key => $value )
			if( gettype($value) == "string" )
			{
				$out[] = "<li class='$li_class'>$key : $value</li>";
			}
			
			elseif( in_array( gettype( $value ), ["object", "array"] ) )
			{
				$out[] = "<li class='$li_class'>$key :</li>";
				$out[] = self::nested_list_to_html( $value );
			}
			
		$out[] = '</ul>'; // End of user-info-list
		
		return implode( $out );
	}
}

$tester = new api_tester();

//echo "Starting test : test_get()\n";
//$res = $tester->test_get();
//echo "Starting test : test_post()\n";
//$res = $tester->test_post();
//echo "Starting test : test_get_users_with_comments()\n";
//$res = $tester->test_get_users_with_comments();
echo "Starting test : test_get_users_post_intro()\n";
$res = $tester->test_get_users_post_intro();

print_r( $res );


?>