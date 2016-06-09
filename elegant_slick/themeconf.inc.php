<?php
/*
Theme Name: Elegant Slick
Version: 0.0.1
Description: Various upates: No category page, thumbnails on picture page, openstreetmap from category on picture page, maximize panoramas
Theme URI: 
Author: Thomas Feuster
Author URI: http://bilder.feuster.com/
*/
$themeconf = array(
  'name'  => 'elegant_slick',
  'parent' => 'elegant',
  'load_parent_css' => true,
  'load_parent_local_head' => true,
  'url' => 'http://bilder.feuster.com/'
);

/*
Panorama in original size
*/
add_event_handler('render_element_content', 'panorama_original_size', EVENT_HANDLER_PRIORITY_NEUTRAL+5);
function panorama_original_size($content, $element_info)
{
  global $conf, $page, $template;
  
  if ( !empty($content) )
  {// we have the image - now lets check & replace if required
    //print_r($element_info);
    
    $width = $element_info['width'];
    $height = $element_info['height'];
    
    // store with for later use...
    if (isset($template->get_template_vars('current')['selected_derivative']))
    {
      $real_image_size = $template->get_template_vars('current')['selected_derivative']->get_size();
    }
    else
    {
      // the hard way... no picture set, e.g. a video
      if (isset($_COOKIE['picture_deriv']))
      {
        if ( array_key_exists($_COOKIE['picture_deriv'], ImageStdParams::get_defined_type_map()) )
        {
          pwg_set_session_var('picture_deriv', $_COOKIE['picture_deriv']);
        }
      }
      $derivative_type = pwg_get_session_var('picture_deriv', $conf['derivative_default_size']);

      // set real size from the dimensions of this derivative - can't think of anything better to do
      $real_image_size = ImageStdParams::get_by_type($derivative_type)->sizing->ideal_size;
    }


    if ($height > 0 && $width/$height > 3.5 && isset($template->get_template_vars('current')['selected_derivative']))
    {
      //print_r('panorama detected');
      //print_r($element_info);
      
      // i) find out what derivative type we have and what max height value it has
      $derivative_type = $template->get_template_vars('current')['selected_derivative']->get_type();
      $ideal_height = ImageStdParams::get_by_type($derivative_type)->sizing->ideal_size[1];
            
      // ii) find derivative with height <= $ideal_height
      foreach($element_info['derivatives'] as $type => $derivative)
      {
        if ($type==IMG_SQUARE || $type==IMG_THUMB)
          continue;
        if (!array_key_exists($type, ImageStdParams::get_defined_type_map()))
          continue;
        // check height against $ideal_height
        if ($derivative->get_size()[1] > $ideal_height)
          continue;
        
        if (!isset($ideal_derivative) || $derivative->get_size()[1] > $ideal_derivative->get_size()[1])
          $ideal_derivative = $derivative;
      }
          
      // now replace the link, the width & height and the usemap from the ideal derivative
      // src="_data/i/galleries/Lissabon 2016/img_5945_dpp_stitch-me.jpg" width="792" height="98" usemap="#mapmedium" 
      
      // 1: image url
      $replacement = 'src="'.$ideal_derivative->get_url().'"';
      $search = '/src=\".*?\"/';
      $content = preg_replace($search, $replacement, $content);
      
      // 2: width
      $replacement = 'width="'.$ideal_derivative->get_size()[0].'"';
      $search = '/width=\".*?\"/';
      $content = preg_replace($search, $replacement, $content);
      $real_image_width = $ideal_derivative->get_size()[0];
      
      // 3: height
      $replacement = 'height="'.$ideal_derivative->get_size()[1].'"';
      $search = '/height=\".*?\"/';
      $content = preg_replace($search, $replacement, $content);
      
      // 4: usemap
      $replacement = 'usemap="'.$ideal_derivative->get_type().'"';
      $search = '/usemap=\".*?\"/';
      $content = preg_replace($search, $replacement, $content);
      
      //print_r($content);
      $real_image_size = $ideal_derivative->get_size();
    }
  
    // store real width for further use
    $template->assign('real_image_size', $real_image_size);
      
    return $content;
  }
}

/*
No category page
*/
add_event_handler('loc_end_index_thumbnails', 'skip_cat');
function skip_cat($tpl_thumbnails_var)
{
  //print_r('skip category');
  global $page;
  if (isset($page['category']))
  {
    redirect($tpl_thumbnails_var[0]['URL']);
  }
}

/*
Picture Thumbnails
*/
add_event_handler('loc_end_picture', 'add_thumbnails_to_template');
function add_thumbnails_to_template()
{
  // stuff borrowed from category_default.inc.php
  // to retrieve template data required for thumbnails
  
  global $template, $conf, $user, $page;
  
  // determine ideal height and set div for image content & image info to it
  if (isset($template->get_template_vars('current')['selected_derivative']))
  {
    $derivative_type = $template->get_template_vars('current')['selected_derivative']->get_type();
  }
  else
  {
    // the hard way... no picture set, e.g. a video
    if (isset($_COOKIE['picture_deriv']))
    {
      if ( array_key_exists($_COOKIE['picture_deriv'], ImageStdParams::get_defined_type_map()) )
      {
        pwg_set_session_var('picture_deriv', $_COOKIE['picture_deriv']);
      }
    }
    $derivative_type = pwg_get_session_var('picture_deriv', $conf['derivative_default_size']);
  }
      
  if (strcmp($derivative_type, 'Original') == 0) {
    // no derivative_type we can use
    if (isset($template->get_template_vars('current')['selected_derivative']))
    {
      // we have at least the size of the image
      $max_image_size = $template->get_template_vars('current')['selected_derivative']->get_size();
    }
    else
    {
      // out of ideas - 'Original' and no selected derivative...
      $max_image_size = ImageStdParams::get_by_type('normal')->sizing->ideal_size;
    }
  }
  else
  {
    $max_image_size = ImageStdParams::get_by_type($derivative_type)->sizing->ideal_size;
  }
  $template->assign('max_image_size', $max_image_size);
  
  // select all pictures for this category
  $query = '
  SELECT *
    FROM '.IMAGES_TABLE.'
    WHERE id IN ('.implode(',', $page['items']).')
    ORDER BY FIELD(id, '.implode(',', $page['items']).')
  ;';

  $result = pwg_query($query);
  
  $pictures = array();
  while ($row = pwg_db_fetch_assoc($result))
  {
    $pictures[] = $row;
  }
  
  trigger_notify('loc_begin_index_thumbnails', $pictures);
  $tpl_thumbnails_var = array();

  foreach ($pictures as $row)
  {    
    $url = duplicate_picture_url(
      array(
        'image_id' => $row['id'],
        'image_file' => $row['file'],
        ),
      array('start')
      );

    $name = render_element_name($row);
    $desc = render_element_description($row, 'main_page_element_description');
    
    $tpl_var = array_merge( $row, array(
      'NAME' => $name,
      'TN_ALT' => htmlspecialchars(strip_tags($name)),
      'TN_TITLE' => get_thumbnail_title($row, $name, $desc),
      'URL' => $url,
      'DESCRIPTION' => $desc,
      'src_image' => new SrcImage($row),
    ) );
    
    $tpl_thumbnails_var[] = $tpl_var;
  }
  //print_r($tpl_thumbnails_var);
  
  $template->assign( array(
    'derivative_params' => trigger_change('get_index_derivative_params', ImageStdParams::get_by_type( pwg_get_session_var('index_deriv', IMG_THUMB) ) ),
    'maxRequests' =>$conf['max_requests'],
    'SHOW_THUMBNAIL_CAPTION' =>$conf['show_thumbnail_caption'],
      ) );
  $template->assign('thumbnails', $tpl_thumbnails_var);

  unset($tpl_thumbnails_var, $pictures);
  //print_r($template);
}

/*
Openstreetmap on picture page
*/
add_event_handler('loc_end_picture', 'add_openstreetmap');
function add_openstreetmap()
{
  global $template, $conf, $page;
  
  // check if openstreetmap is installed & enabled for category page
  if ($conf['osm_conf']['category_description']['enabled'])
  {
    include_once(OSM_PATH.'category.inc.php');
    
    // trick openstreetmap into thinking its a category page...
    $cur_image_id = $page['image_id'];
    unset($page['image_id']);
    osm_render_category();
    $page['image_id'] = $cur_image_id;
    
    // add button to show/hide map
    $content = '<a id="mapSwitcher" title="Show map" ';
    $content .= 'class="pwg-state-default pwg-button" rel="nofollow">';
    $content .= '<span class="pwg-icon pwg-icon-globe"></span>';
    $content .= '<span class="pwg-button-text">Show map</span>';
    $content .= '</a>';
    $template->add_picture_button($content);
    // code for this is included in picture.tpl
    
    //print_r('rendered');
  }
}

?>
