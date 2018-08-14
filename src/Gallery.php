<?php

namespace Bezbeli;

class Gallery
{
    public function __construct()
    {
        remove_shortcode('gallery');
        add_shortcode('gallery', __NAMESPACE__.'\\blueimp_gallery');
    }

    public function blueimpGallery($attr)
    {
        static $instance = 0;
        ++$instance;

        $id = get_the_id();
        $columns = $attr['columns'];
        $size = $attr['size'];
        $order = $attr['order'];
        $orderby = $attr['orderby'];
        $link = '';

        $columns = (0 == 12 % $columns) ? $columns : 3;
        $grid = sprintf('col-%1$s col-sm-%1$s col-md-%1$s', 12 / $columns);

        $attachments = get_children([
                'post_parent' => $id,
                'post_status' => 'inherit',
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'orderby' => $orderby,
                'order' => $order,
            ]);

        if (empty($attachments)) {
            return '';
        }

        $unique = (get_query_var('page')) ? $instance.'-p'.get_query_var('page') : $instance;
        $output = '';

        /*
        OUTPUT blueimp-gallery code ONLY ONCE, we dont need
        it more than ONCE if there are multiple galleries
        on same page
        */

        // if ($unique == 1) {
        $output .= '
        <div id="blueimp-gallery" class="blueimp-gallery">
            <div class="slides"></div>
            <h3 class="title"></h3>
            <a class="prev">‹</a>
            <a class="next">›</a>
            <a class="close">×</a>
            <a class="play-pause"></a>
            <ol class="indicator"></ol>
        </div>
        ';
        // }
        $output .= '<div id="links" class="gallery">';

        $i = 0;

        foreach ($attachments as $id => $attachment) {
            switch ($link) {
                case 'file':
                    $image = wp_get_attachment_link($id, $size, false, false);
                    break;
                case 'none':
                    $image = wp_get_attachment_image($id, $size, false, ['class' => 'thumbnail img-thumbnail']);
                    break;
                default:
                    $image = '<a href="'.wp_get_attachment_image_src($id, 'large')[0].'" title="'.$attachment->post_title.'" data-gallery data-description="'.$attachment->post_excerpt.'">';
                    $image .= wp_get_attachment_image($id, $size, false, ['class' => 'thumbnail img-thumbnail']);
                    $image .= '</a>';
                    break;
            }

            $output .= (0 == $i % $columns) ? '<div class="row justify-content-center">' : '';
            $output .= '<div class="'.$grid.' mb-4">'.$image;

            if (trim($attachment->post_excerpt)) {
                $output .= '<div class="caption hidden">'.wptexturize($attachment->post_excerpt).'</div>';
            }

            $output .= '</div>';
            ++$i;
            $output .= (0 == $i % $columns) ? '</div>' : '';
        }

        $output .= (0 != $i % $columns) ? '</div>' : '';
        $output .= '</div>';

        return $output;
    }
}
