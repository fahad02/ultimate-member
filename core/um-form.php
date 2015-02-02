<?php

class UM_Form {

	public $form_suffix;
	
	function __construct() {
	
		$this->post_form = null;

		$this->form_suffix = null;
		
		$this->errors = null;
		
		add_action('init', array(&$this, 'form_init'), 2);
		
		add_action('init', array(&$this, 'field_declare'), 10);
		
	}
	
	/***
	***	@add errors
	***/
	function add_error( $key, $error ) {
		if ( !isset( $this->errors[$key] ) ){
			$this->errors[$key] = $error;
		}
	}
	
	/***
	***	@has error
	***/
	function has_error( $key ) {
		if ( isset($this->errors[$key]) )
			return true;
		return false;
	}
	
	/***
	***	@declare all fields
	***/
	function field_declare(){
		global $ultimatemember;
		$this->all_fields = $ultimatemember->builtin->custom_fields;
	}
	
	/***
	***	@Checks that we've a form
	***/
	function form_init(){
		global $ultimatemember;
		
		$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
		
		if ( $http_post && !is_admin() && isset( $_POST['form_id'] ) && is_numeric($_POST['form_id']) ) {
			
			$this->form_id = $_POST['form_id'];
			$this->form_status = get_post_status( $this->form_id );
			
			if ( $this->form_status == 'publish' ) {

				/* save entire form as global */
				$this->post_form = $_POST;
				
				$this->post_form = $this->beautify( $this->post_form );
				
				$this->form_data = $ultimatemember->query->post_data( $this->form_id );
				
				$this->post_form['submitted'] = $this->post_form;
				
				$this->post_form = array_merge( $this->form_data, $this->post_form );

				if ( $_POST[ $ultimatemember->honeypot ] != '' )
					wp_die('Hello, spam bot!');
				
				if ( !in_array( $this->form_data['mode'], array('login') ) ) {
				
					$form_timestamp  = trim($_POST['timestamp']);
					$live_timestamp  = time();
					
					if ( $form_timestamp == '' )
						wp_die( __('Hello, spam bot!') );

					if ( $live_timestamp - $form_timestamp < 3 )
						wp_die( __('Whoa, slow down! You\'re seeing this message because you tried to submit a form too fast and we think you might be a spam bot. If you are a real human being please wait a few seconds before submitting the form. Thanks!') );

				}
				
				/* Continue based on form mode - pre-validation */
				
				do_action('um_submit_form_errors_hook', $this->post_form );

				do_action("um_submit_form_{$this->post_form['mode']}", $this->post_form );

			}
			
		}

	}
	
	/***
	***	@Beautify form data
	***/
	function beautify( $form ){
	
		if (isset($form['form_id'])){
		
			$this->form_suffix = '-' . $form['form_id'];
			
			foreach($form as $key => $value){
				if (strstr($key, $this->form_suffix) ) {
					$a_key = str_replace( $this->form_suffix, '', $key);
					$form[$a_key] = $value;
					unset($form[$key]);
				}
			}
		
		}
		
		return $form;
	}
	
	/***
	***	@Display Form Type as Text
	***/
	function display_form_type($mode, $post_id){
		$output = null;
		switch($mode){
			case 'login':
				$output = 'Login';
				break;
			case 'profile':
				$output = 'Profile';
				break;
			case 'register':
				$output = 'Register';
				break;
		}
		return $output;
	}
	
}