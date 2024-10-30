<?php 
/*
* Magic Inliner
*
* @package     Magic Inliner
* @author      Nora
* @copyright   2016 Nora https://wp-works.com
* @license     GPL-2.0+
* 
* @wordpress-plugin
* Plugin Name: Magic Inliner
* Plugin URI: https://wp-works.com
* Description: Made this for my exercise, no more updates. Change CSS and JS tags printed by Wordpress Enqueue into inline codes.
* Version: 1.0.16
* Author: Nora
* Author URI: https://wp-works.com
* Text Domain: magic-inliner
* Domain Path: /languages/
* License: GPL-2.0+
* License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( is_admin() || is_customize_preview() ) return; 

class MagicInliner {

	function __construct() {
		$this->initHooks();
	}

	protected function initHooks()
	{
		add_filter( 'style_loader_tag', array( $this, 'styleInline' ), 1, 3 );
		add_filter( 'script_loader_tag', array( $this, 'scriptInline' ), 1, 3 );
	}

	public function styleInline( $tag, $handle, $src )
	{
		if ( ! is_string( $tag ) || empty( $tag ) ) return '';
		if ( false !== strpos( $src, get_template_directory_uri() ) ) return $tag;
		$data = $this->getAbsContents( $src, file_get_contents( $src ) );
		if ( false !== $data ) {
			wp_add_inline_script( $handle, $data );
			add_action( 'wp_footer', function() use ( $handle, $data ) {
				echo "<style id=\"mi-style-{$handle}\" type=\"text/css\">{$data}</style>" . PHP_EOL;
			}, 100 );
			return '';
		}
		return $tag;
	}

	public function scriptInline( $tag, $handle, $src )
	{
		if ( ! is_string( $tag ) || empty( $tag ) ) return '';
		if ( false !== strpos( $src, get_template_directory_uri() ) || 'jquery' === $handle ) return $tag;
		$data = file_get_contents( $src );
		if ( false !== $data ) {
			add_action( 'wp_footer', function() use ( $handle, $data ) {
				echo "<script id=\"mi-script-{$handle}\" type=\"text/javascript\">{$data}</script>" . PHP_EOL;
			}, 100 );
			return;
		}
		return $tag;
	}

	function getAbsContents( $path, $content ) {

		// ファイル名を取得
		if( strpos( $path, '?' ) ) {
			preg_match( '/([^\/\?]+)[\?]/i', $path, $file_name );
		} else {
			preg_match( '/([^\/]+)$/i', $path, $file_name );
		}
		$this->mi_file_name = $file_name[ 1 ];
		//echo $this->mi_file_name . PHP_EOL; // チェック用

		// ファイルディレクトリを取得
		preg_match( '/(https?:\/\/([^\/]+\/)+)([^\/]+)$/i', $path, $directory_to_css );
		$this->mi_directory_to_file = $directory_to_css[ 1 ];
		//echo $this->mi_directory_to_file . PHP_EOL; // チェック用

		//echo $content;

		$abs_content = preg_replace_callback(
			'/(url\([\'"]?)([^\'"\)]+)([\'"]?\))/',
			array( $this, 'replaceToAbs' ),
			$content
		);

		return $abs_content;

	}

	function replaceToAbs( $url_matched ) {

		$src_beginning = $url_matched[ 1 ];

		$src_url = $url_matched[ 2 ];
		
		$src_ending = $url_matched[ 3 ];


		if( strpos( $src_url, '//' ) === 0 
			|| strpos( $src_url, 'http' ) === 0 
			|| strpos( $url_matched[ 0 ], 'url(data:' ) !== false
		) {
			return $url_matched[ 0 ];
		}

		//print_r( $src_url );

		//echo $this->mi_file_name . PHP_EOL;
		//echo $this->mi_directory_to_file . PHP_EOL;

		$relative_back = substr_count( $src_url, '../' );

		if( $relative_back > 0 ) { 
			for( $i = 0; $i < $relative_back; $i++ ) {
				$mi_directory_to_file = preg_replace( 
					'/(\/)[^\/]+[\/]$/i',
					'${1}',
					$this->mi_directory_to_file
				);
				$src_url = str_replace( '../', '', $src_url );
			}
		} else {
			$mi_directory_to_file = $this->mi_directory_to_file;
		}

		//echo $this->mi_directory_to_file .PHP_EOL;

		$abs_path = $mi_directory_to_file . $src_url;

		$return = $src_beginning . $abs_path . $src_ending;

		return $return;

	}


}

new MagicInliner;

	
