<?php
/**
 * Plugin Name: SWP - REST API
 * Description: Pull entries from an external site
 * Version: 2.0
 * Author: Jake Almeda
 * Author URI: http://smarterwebpackages.com/
 * Network: true
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_shortcode( 'setup-feature-pull', 'setup_feature_pull_function' );
function setup_feature_pull_function( $atts ) {
    // $atts['foo'] -> get attribute contents

    // do not run in WP-Admin
    if( is_admin() ) return;

    
    // variables | URL
    if( array_key_exists( "url", $atts ) ) {
        $url = $atts[ 'url' ];
    }
    
    // variables | Native or Custom field (yes or no)
    if( array_key_exists( "id", $atts ) ) {
        $id = $atts[ 'id' ];
    }
    
    // variables | Field
    if( array_key_exists( "field", $atts ) ) {
        $field = $atts[ 'field' ];
    }
    
    // variables | Field
    if( array_key_exists( "template", $atts ) ) {
        $template = $atts[ 'template' ];
    }
    
    // variables | Size
    if( array_key_exists( "size", $atts ) ) {
        $img_size = $atts[ 'size' ];
    } else {
        // assign default size
        $img_size = 'thumbnail';
    }
    
    // variables | Class (CSS)
    if( array_key_exists( "class", $atts ) ) {
        $styling = "class='".$atts[ 'class' ]."'";
    } else {
        // assign default size
        $styling = '';
    }
    
    // variables | Block
    if( array_key_exists( "block", $atts ) ) {
        $block = $atts[ 'block' ];
    }

    /*
    http://test.jakealmeda.com/wp-json/wp/v2/posts/1
    http://test.jakealmeda.com/wp-json/acf/v3/posts
    */

    $rest_api_url_extension = 'wp'; // or 'acf'
    $post_type = 'posts';
    $version = 'v2';
    //echo rtrim( $url, "/" ).'/wp-json/'.$rest_api_url_extension.'/'.$version.'/'.$post_type.'/'.$id;

    $url_combined = rtrim( $url, "/" ).'/wp-json/'.$rest_api_url_extension.'/'.$version.'/';

    //$target = 'http://plan.smarterwebpackages.com/wp-json/wp/v2/partners';
    //$target = 'http://plan.smarterwebpackages.com/wp-json/wp/v2/partners/170';
    //if( $id ) {
    $target = file_get_contents( $url_combined.$post_type.'/'.$id );
    /*} else {
        $target = file_get_contents( rtrim( $url, "/" ).'/wp-json/'.$rest_api_url_extension.'/'.$version.'/'.$post_type );
    }*/

    $array = json_decode( $target, TRUE, 512 );

    // validate URLs
    if( empty( $url ) ) {

        return "Please specify the source site (URL).";

    } else{

        if( empty( $id ) ) {

            return "Please specify the post ID you want to retrieve from.";

        } else {
            
            if( empty( $field ) ) {

                if( empty( $template ) ) {

                    return "Please specify field you want to retrieve or specify the template name to get a group of information from the source.";

                } else {

                    return setup_dig_for_template( $array, $template );

                }

            } else {

                if( is_array( $array ) ) {
                    //var_dump( $array );
                    foreach( $array as $key => $value ) {
                        
                        /*
                        // show all array entries
                        echo '<h2>'.$key.'</h2> == ';
                        if( is_array( $value ) ) {
                            foreach( $value as $k => $v ) {
                                echo '======== '.$k.': ';
                                if( is_array( $v ) ) {
                                    foreach($v as $e => $a) {
                                        echo $e.' | ';
                                        if( is_array( $a ) ) {
                                            foreach($a as $y => $l) {
                                                echo $y.' ___ '.$l.'<br />';
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            echo $value.'<br />';
                        } echo '<hr />';
                        */

                        /*
                        // USE THE IF STATEMENT BELOW IF ACF CUSTOM FIELDS ARE NOT INCLUDED IN POSTS
                        if( $key == 'acf' && is_array( $value ) ) {

                            foreach( $value as $keys => $values ) {
                                
                                //echo $keys.' == '.$values;
                                if( $keys == $field ) {
                                    return $values;
                                }

                            } // end of foreach( $value as $keys => $values ) {

                        }*/

                        // NATIVE FIELD
                        if( $key == $field ) {
                            
                            if( is_array( $value ) ) {

                                if( array_key_exists( "rendered", $value ) ) {

                                    return $value[ 'rendered' ];

                                }

                            } else {

                                // FEATURED IMAGE
                                if( $field == 'featured_media' ) {

                                    // http://test.jakealmeda.com/wp-json/wp/v2/media/155
                                    return '<img src="'.setup_dig_for_the_image( $url_combined, $value, $img_size ).'" '.$styling.' />';
                                    
                                } else {

                                    return $value;

                                }
                                
                            }

                            // field found | set stopper for custom field
                            $stop = 1;

                        }

                        // CUSTOM FIELD | ACF
                        if( $key == 'acf' && $stop != 1 ) {

                            if( is_array( $value ) ) {

                                foreach( $value as $keys => $values ){
                                    
                                    if( $keys == $field ) {
                                        //return $values;

                                        if( is_numeric( $values ) ) {

                                            $url_404 = setup_check_for_404( $url_combined.'media/'.$values );

                                            if( isset( $url_404 ) === TRUE ) {
                                                return '<img src="'.setup_dig_for_the_image( $url_combined, $values, $img_size ).'" '.$styling.' />';
                                            } else {
                                                return 'No image found from source.';
                                            }

                                        } else {

                                            if( is_array( $values ) ) {

                                                $dig_gallery = setup_dig_for_the_gallery( $values, $img_size );

                                                foreach ($dig_gallery as $ky) {
                                                    $out .= '<img src="'.$ky.'" '.$styling.' />';
                                                }

                                                return $out;

                                            } else {

                                                return $values;

                                            }

                                        }

                                    }

                                }

                            }

                        }

                    } // end of foreach( $array as $key => $value ) {

                } // end of if( is_array( $array ) ) {

            } // end of: if( empty( $field ) ) {

        } // end of: if( empty( $id ) ) {

    }

}

if( !function_exists( 'setup_check_for_404' ) ) {

    function setup_check_for_404( $url ) {
          
        // Getting page header data 
        $array = @get_headers($url); 
          
        // Storing value at 1st position because 
        // that is only what we need to check 
        $string = $array[0]; 
          
        // 404 for error, 200 for no error 
        if(strpos($string, "200")) { 
            //echo 'Specified URL Exists'; 
            return TRUE;
        }  
        else { 
            //echo 'Specified URL does not exist'; 
            return FALSE;
        }

    }

}

if( !function_exists( 'setup_dig_for_the_image' ) ) {

    function setup_dig_for_the_image( $url_combined, $value, $img_size ) {

        $media_rest_url = $url_combined.'media/'.$value;

        $target_media = file_get_contents( $media_rest_url );

        $array_media = json_decode( $target_media, TRUE, 512 );

        foreach( $array_media as $m_key => $m_value ) {
            /*
            echo '<h2>'.$m_key.'</h2>';
            if( is_array( $m_value ) ) {
                var_dump( $m_value );
            } else {
                echo $m_value;
            }
            */

            if( $m_key == 'media_details' ) {

                foreach( $m_value as $md_key => $md_value ) {

                    //echo '<h2>'.$md_key.'</h2>';

                    if( $md_key == 'sizes' ) {

                        if( is_array( $md_value ) ) {

                            // filter what size to use
                            foreach ($md_value as $mdv_key => $mdv_value) {
                                    
                                //echo '<h3>'.$mdv_key.'</h3>';

                                // validate the image size to use
                                if( $mdv_key == $img_size ) {

                                    if( is_array( $mdv_value ) ) {
                                        return $mdv_value[ "source_url" ];
                                        //break 3; // stop the first foreach loop
                                    }

                                }

                            } // end of foreach ($md_value as $mdv_key => $mdv_value) {

                        }

                    } // end of if( $md_key == 'sizes' ) {

                }

            } // end of if( $m_key == 'media_details' ) {

        } // end of foreach( $array_media as $m_key => $m_value ) {

    }

}

if( !function_exists( 'setup_dig_for_the_gallery' ) ) {

    function setup_dig_for_the_gallery( $contents, $img_size = 'thumbnail' ) {

        $return = array();

        foreach( $contents as $val ) {

            if( is_array( $val ) ) {

                foreach($val as $key => $value) {
                    
                    if( $key == 'sizes' ) {
                        
                        if( is_array( $value ) ) {
                            
                            foreach( $value as $k => $v ) {
                                // filter the size and add to an array
                                if( $k == $img_size ){
                                    $return[] = $v;
                                }

                            }

                        }
                        
                    }
                    
                }

            }

        }

        return $return;

    }

}

if( !function_exists( 'setup_dig_for_template' ) ) {

    function setup_dig_for_template( $array, $template ) {

        $collate_output = array();

        //var_dump( $array ); echo '<hr />'.$template;
        if( is_array( $array ) ) {

            foreach( $array as $key => $value ) {
                //echo '<h2>'.$key.'</h2>';
                
                if( is_array( $value ) ) {

                    if( array_key_exists( "rendered", $value ) ) {

                        //echo '<h3>1</h3>';
                        $collate_output[ $key ] = $value[ 'rendered' ];

                    } else {

                        //echo '<h3>2</h3>';
                        foreach( $value as $keys => $values ) {
                            //echo $keys.' | '.$values.'<hr />';
                            if( is_array( $values ) ) {
                                //echo '<h3>2.1</h3>';
                                foreach ($values as $k => $v) {
                                    echo '<h4>'.$k.'</h4>'; var_dump($v);
                                }

                            } else {

                                $collate_output[ $keys ] = $values;

                            }
                        }

                    }

                } else {
                    //echo '<h3>3</h3>';
                    $collate_output[ $key ] = $value;
                }

            }

        }

    }

}

