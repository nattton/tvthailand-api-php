<?php


foreach ($programs as $key => $value) {

	// $class = (($key%2)==0)?'panel1':'panel2';

	$image_properties = array(
          'src' => $thumbnail_path.$value->thumbnail,
          'alt' => $value->title,
          'class' => 'post_image',
          'width' => '100',
          'height' => '80',
          'title' => $value->title,
          'rel' => 'lightbox',
	);
	// echo (($key%2)==0)?'<div class="quickFlip">':'';
	echo img($image_properties);
	// echo (($key%2)!=0)?'</div>':'';
}

// echo $this->table->generate();

?>