<?php 
namespace SPUI\Replacer\Classes; 


if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;


class Updater
{

    protected static $updatesNumber = 0; 

    public function updatePost($post_id, $content)
    {
        global $wpdb; 

        $result = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts} SET post_content = %s WHERE ID = %d",
                $content,
                $post_id
            )
        );
    
        if ($result === false) {
            // Notice::addError('Something went wrong while replacing' .  $result->get_error_message() );
            Log::addError('WP-Error during post update', $result);
            return false; 
        }

        self::$updatesNumber++; 
        return true;
    }



}
