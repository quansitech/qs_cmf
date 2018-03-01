<?php
namespace Common\Util;

class QrCode{
    private $_font_size = 13;
    private $_bottom_text;
    private $_qrcode_size = 10;
    private $_qrcode_margin = 1;
    private $_font_file;
    private $_center_img_path_or_url;

    private $_bottom_text_interval = 5;
    private $_bottom_text_top_margin = 15;
    private $_bottom_text_bottom_margin = 0;
    private $_bottom_text_margin_lr = 10;

    private $_arc_fact = 0.3; //圆角的弧度比率 0.5即为圆形
    private $_img_attr;


    public function __construct(){
        import('Common.Util.phpqrcode.qrlib');
        $font_path = APP_DIR . '/Ttf/msyh.ttf';
        if(file_exists($font_path)){
            $this->_font_file = $font_path;
        }
        else{
            E('缺少字体文件msyh.ttf');
        }
    }

    //在二维码底部设置中文文字
    public function setBottomText($text){
        $this->_bottom_text = $text;
        return $this;
    }

    public function setBottomTextTopMargin($top_margin){
        $this->_bottom_text_top_margin = $top_margin;
    }

    public function setFontSize($size){
        $this->_font_size = $size;
        return $this;
    }

    public function setQrcodeSize($qrcode_size){
        $this->_qrcode_size = $qrcode_size;
        return $this;
    }

    //style 参数为一个固定格式数组
    //['type' => 'gradient','cate' => 'diagonal', 'start_color' => [0,139,0], 'end_color' => [127,255,0]] 为对角线渐变
    //['type' => 'gradient','cate' => 'virtical', 'start_color' => [0,139,0], 'end_color' => [127,255,0]] 为垂直渐变
    //['type' => 'fixed','color' => [0,139,0]] 固定二维颜色
    public function setQrCodeStyle($style){
        $this->_img_attr['qrcode']['style'] = $style;
        return $this;
    }

    //shape = circle 圆形  shape = '' 不做二次渲染
    //border 图片边框大小
    //border_color 边框颜色 [$red, $green, $blue]
    public function setImage($path_or_url,$shape = '', $width = 150, $height = 150, $border = 5, $border_color = [255, 255, 255]){
        $this->_img_attr['center_img']['path_or_url'] = $path_or_url;
        $this->_img_attr['center_img']['width'] = $width;
        $this->_img_attr['center_img']['height'] = $height;
        $this->_img_attr['center_img']['shape'] = $shape;
        $this->_img_attr['center_img']['border'] = $border;
        $this->_img_attr['center_img']['border_color'] = $border_color;
        return $this;
    }

    public function png($qrcode_str){
        $tmp_file = '//tmp//' . guid() . '.png';
        \QRcode::png($qrcode_str, $tmp_file, QR_ECLEVEL_H, $this->_qrcode_size, $this->_qrcode_margin);


        $im = $this->_genPng($tmp_file);
        header('Content-Type:image/png');
        imagepng($im);
        imagedestroy($im);
        unlink($tmp_file);
    }

    private function _genPng($qrcode_tmp_file){
        list($qrcode_width, $qrcode_height) = GetImageSize($qrcode_tmp_file);
        $this->_img_attr['qrcode']['width'] = $qrcode_width;
        $this->_img_attr['qrcode']['height'] = $qrcode_height;

        if($this->_bottom_text){
            $this->_calcBottomText($qrcode_width, $qrcode_height);
        }

        $qrcode_im = imagecreatefrompng($qrcode_tmp_file);

        //配置二维码表现风格
        if($this->_img_attr['qrcode']['style']){
            $qrcode_im = $this->_renderQrCode($qrcode_im);
        }
        $new_im = $this->_imageCreate();
        imagecopymerge($new_im, $qrcode_im, 0, 0, 0, 0, $qrcode_width, $qrcode_height, 100);
        if($this->_bottom_text){
            $new_im = $this->_renderBottomText($new_im);
        }
        $new_im = $this->_renderCenterImg($new_im);
        imagedestroy($qrcode_im);
        return $new_im;
    }

    private function _renderQrCode($im){

        $this->_setQrCodeTransparent($im);
        $bg_im = $this->_genBgImage($this->_img_attr['qrcode']['style'], $this->_img_attr['qrcode']['width'], $this->_img_attr['qrcode']['height']);

        imagecopyresampled($bg_im, $im, 0, 0, 0, 0, $this->_img_attr['qrcode']['width'], $this->_img_attr['qrcode']['height'], $this->_img_attr['qrcode']['width'], $this->_img_attr['qrcode']['height']);
        ImageDestroy($im);

        return $bg_im;
    }

    private function _renderCenterImg($im){
        if($this->_img_attr['center_img']){
            $center_im = $this->_getCenterImgRes();

            //形状渲染
            switch($this->_img_attr['center_img']['shape']){
                case 'circle':
                  $this->_arc_fact = 0.5;
                  $center_im = $this->_renderToRound($center_im);
                  break;
                case 'round_rect':
                  $center_im = $this->_renderToRound($center_im);
                  break;
                default:
                  $center_im = $this->_renderToRect($center_im);
                  break;
            }

            $center_im_width = imagesx($center_im);
            $center_im_high = imagesy($center_im);

            $dst_x = $this->_img_attr['qrcode']['width'] / 2 - $center_im_width / 2;
            $dst_y = $this->_img_attr['qrcode']['height'] / 2 - $center_im_high / 2;

            //imagecopymerge($im, $center_im, $dst_x, $dst_y, 0, 0, $this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['height'], 100);

            imagecopyresampled($im, $center_im, $dst_x, $dst_y, 0, 0, $center_im_width, $center_im_high, $center_im_width, $center_im_high);

            imagedestroy($center_im);
        }

        return $im;
    }

    private function _getCenterImgRes(){
        list($src_width, $src_height) = GetImageSize($this->_img_attr['center_img']['path_or_url']);

        $thumb = imagecreatetruecolor($this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['height']);
        imagesavealpha($thumb, true);
        $source = $this->_imageCreateFrom($this->_img_attr['center_img']['path_or_url']);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['height'], $src_width, $src_height);
        imagedestroy($source);
        return $thumb;
    }

    private function _renderBottomText($im){
        $bgc = imagecolorallocate($im, 0, 0, 0);

        $acc_height = 0;
        foreach($this->_img_attr['bottom_text']['items'] as $v){
            $x = ($this->_img_attr['qrcode']['width'] - $v['width']) / 2;
            $y = $this->_img_attr['qrcode']['height'] + $this->_bottom_text_top_margin + $acc_height;
            $acc_height += $v['height'] + $this->_bottom_text_interval;
            imagefttext($im, $this->_font_size, 0, $x, $y, $bgc, $this->_font_file, $v['text']);
        }
        return $im;
    }

    private function _imageCreate(){
        $width = $this->_img_attr['qrcode']['width'];
        $height = $this->_img_attr['qrcode']['height'] + $this->_bottom_text_top_margin + $this->_img_attr['bottom_text']['height'] + $this->_bottom_text_bottom_maigin;
        $im = imagecreatetruecolor($width, $height);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $bgc);
        return $im;
    }

    private function _imageCreateFrom($path_or_url){
        $type = exif_imagetype($path_or_url);
        if($type === false){
            E('exif_imagetype: 不是一张有效图片');
        }

        switch ($type) {
          case 1:
              return imagecreatefromgif($path_or_url);
          case 2:
              return imagecreatefromjpeg($path_or_url);
          case 3:
              return imagecreatefrompng($path_or_url);
          case 6:
              return imagecreatefrombmp($path_or_url);
          case 15:
              return imagecreatefromwbmp($path_or_url);
          case 16:
              return imagecreatefromxbm($path_or_url);
          default:
              E("type={$type} 类型无法处理.");
              break;
        }
    }

    private function _calcBottomText($qrCode_width, $qrcode_height){
        $text_line_arr = [];
        $text_line_arr = $this->_genTextLine($qrCode_width, $qrcode_height, $this->_bottom_text, $text_line_arr);


        list($text_box_width, $text_box_height) = $this->_getTextBoxSize($this->_bottom_text);

        $height = 0;
        foreach($text_line_arr as $v){
            $height += $v['height'] + $this->_bottom_text_interval;
        }
        $height -= $this->_bottom_text_interval;

        $this->_img_attr['bottom_text'] = array(
            'items' => $text_line_arr,
            'height' => $height
        );
    }

    private function _genTextLine($qrCode_width, $qrcode_height , $text, $text_line_arr){
      $text_len = mb_strlen($text, 'utf-8');

      list($text_box_width, $text_box_height) = $this->_getTextBoxSize($text);

      //每个字符长度
      $per_char_width = $text_box_width / $text_len;

      //每行可存放的字符数量  (二维码边框宽度-文字与左右边框的间隔空隙) / 单个字符长度
      $char_num_per_line = intval(($qrCode_width - ($this->_bottom_text_margin_lr) * 2) / $per_char_width);

      if($text_len > $char_num_per_line){
          $f_text = mb_substr($text, 0, $char_num_per_line, 'utf-8');
          $r_text = mb_substr($text, $char_num_per_line, $text_len - $char_num_per_line, 'utf-8');
          $arr = array('text' => $f_text, 'width' => mb_strlen($f_text, 'utf-8') * $per_char_width, 'height' => $text_box_height);
          array_push($text_line_arr, $arr);
          $text_line_arr = $this->_genTextLine($qrCode_width, $qrcode_height, $r_text, $text_line_arr);
      }
      else{
          $arr = array('text' => $text, 'width' => $text_len * $per_char_width, 'height' => $text_box_height);
          array_push($text_line_arr, $arr);
      }
      return $text_line_arr;
    }

    private function _getTextBoxSize($text){
        //l = left, b=bottom, r=right, t=top, lb=left_bottom 左下角
        list($lb_x, $lb_y, $rb_x, $rb_y, $rt_x, $rt_y, $lt_x, $lt_y) = ImageTTFBBox($this->_font_size, 0, $this->_font_file, $text);
        $width = $rb_x - $lb_x;
        $height = $lb_y - $lt_y;
        return array($width, $height);
    }

    private function _renderToRect($im){
        $width = $this->_img_attr['center_img']['width'] + $this->_img_attr['center_img']['border'] * 2;
        $height = $this->_img_attr['center_img']['height'] + $this->_img_attr['center_img']['border'] * 2;
        $tmp_im = imagecreatetruecolor($width, $height);
        if($this->_img_attr['center_img']['border_color']){
            list($r, $g, $b) = $this->_img_attr['center_img']['border_color'];
            $bgc = imagecolorallocate($tmp_im, $r, $g, $b);
        }
        else{
            $bgc = imagecolorallocate($tmp_im, 255, 255, 255);
        }

        imagefill($tmp_im, 0, 0, $bgc);

        imagecopyresampled($tmp_im, $im, $this->_img_attr['center_img']['border'], $this->_img_attr['center_img']['border'], 0, 0, $this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['height'], $this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['height']);

        imagedestroy($im);
        return $tmp_im;
    }


    //渲染圆角正方形
    private function _renderToRound($im){
      $w = $this->_img_attr['center_img']['width'] + $this->_img_attr['center_img']['border'] * 2;
      $h = $this->_img_attr['center_img']['height'] + $this->_img_attr['center_img']['border'] * 2;
      $t_im = imagecreatetruecolor($w, $h);
      imagesavealpha($t_im, true);
      $bg = imagecolorallocatealpha($t_im, 255, 255, 255, 127);
      imagefill($t_im, 0, 0, $bg);
      list($r, $g, $b) = $this->_img_attr['center_img']['border_color'];
      $border_color = imagecolorallocate($t_im, $r, $g, $b);
      $white = imagecolorallocate($t_im, 255, 255, 255);
      $black = imagecolorallocate($t_im, 0, 0, 0);

      if($this->_img_attr['center_img']['border']){
          //新建一模具图，套出圆角形状
          $new_round_im = imagecreatetruecolor($this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['width']);
          imagesavealpha($new_round_im, true);
          $new_round_white = imagecolorallocate($new_round_im, 255, 255, 255);
          $new_round_bg = imagecolorallocatealpha($new_round_im, 255, 255, 255, 127);
          $new_round_im = $this->_drawRoundRect($new_round_im, $this->_img_attr['center_img']['width'], $this->_arc_fact, $new_round_white);
          imagefill($new_round_im, $w/2, $w/2, $new_round_bg);

          imagecopyresampled($im, $new_round_im, 0, 0, 0, 0, $this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['width']);
          $im_bg = imagecolorallocatealpha($im, 255, 255, 255, 127);
          imagefill($im, 1, 1, $im_bg);
          imagefill($im, $this->_img_attr['center_img']['width']-1, 1, $im_bg);
          imagefill($im, 1, $this->_img_attr['center_img']['width']-1, $im_bg);
          imagefill($im, $this->_img_attr['center_img']['width']-1, $this->_img_attr['center_img']['width']-1, $im_bg);

          $t_im = $this->_drawRoundRect($t_im, $w, $this->_arc_fact, $border_color);

          imagecopymerge($t_im, $im, $this->_img_attr['center_img']['border'], $this->_img_attr['center_img']['border'], 0,0, $this->_img_attr['center_img']['width'], $this->_img_attr['center_img']['width'], 100);

          imagedestroy($im);
          Imagedestroy($new_round_im);
          return $t_im;
      }
      else{
          imagefill($t_im, 0, 0, $black);
          $t_im = $this->_drawRoundRect($t_im, $w, $this->_arc_fact, $white);
          imagefill($t_im, $w / 2, $w / 2, $bg);

          imagecopyresampled($im, $t_im, 0, 0, 0, 0, $w, $w, $w, $w);
          $im_bg = imagecolorallocatealpha($im, 255, 255, 255, 127);
          imagefill($im, 1, 1, $im_bg);
          imagefill($im, $w-1, 1, $im_bg);
          imagefill($im, 1, $w-1, $im_bg);
          imagefill($im, $w-1, $w-1, $im_bg);
          imagedestroy($t_im);
          return $im;
      }
    }

    private function _drawRoundRect($im, $width, $arc_fact, $bg){
        $o_w = imagesx($im);
        $arc_r = $width * $arc_fact;

        $border_w = ($o_w - $width) / 2;
        $center_rect_x1 = $border_w + $arc_r;
        $center_rect_y1 = $border_w;
        $center_rect_x2 = $o_w - $border_w - $arc_r;
        $center_rect_y2 = $o_w - $border_w;
        imagefilledrectangle($im, $center_rect_x1, $center_rect_y1, $center_rect_x2, $center_rect_y2, $bg);

        $left_rect_x1 = $border_w;
        $left_rect_y1 = $border_w + $arc_r;
        $left_rect_x2 = $border_w + $arc_r;
        $left_rect_y2 = $o_w - $border_w - $arc_r;
        imagefilledrectangle($im, $left_rect_x1, $left_rect_y1, $left_rect_x2, $left_rect_y2, $bg);

        $right_rect_x1 = $o_w - $border_w - $arc_r;;
        $right_rect_y1 = $border_w + $arc_r;
        $right_rect_x2 = $o_w - $border_w;
        $right_rect_y2 = $o_w - $border_w - $arc_r;
        imagefilledrectangle($im, $right_rect_x1, $right_rect_y1, $right_rect_x2, $right_rect_y2, $bg);

        $lt_x = $border_w + $arc_r;
        $lt_y = $border_w + $arc_r;
        $rt_x = $o_w - $border_w - $arc_r;
        $rt_y = $border_w + $arc_r;
        $lb_x = $border_w + $arc_r;
        $lb_y = $o_w - $border_w - $arc_r;
        $rb_x = $o_w - $border_w - $arc_r;
        $rb_y = $o_w - $border_w - $arc_r;

        $r2 = $arc_r * 2;

        imagefilledarc($im, $lt_x, $lt_y, $r2, $r2, 180, 270, $bg, IMG_ARC_PIE);
        imagefilledarc($im, $rt_x, $rt_y, $r2, $r2, 270, 360, $bg, IMG_ARC_PIE);
        imagefilledarc($im, $lb_x, $lb_y, $r2, $r2, 90, 180, $bg, IMG_ARC_PIE);
        imagefilledarc($im, $rb_x, $rb_y, $r2, $r2, 0, 90, $bg, IMG_ARC_PIE);

        return $im;
    }

    private function _genBgImage($style, $width, $high){
        $im = imagecreatetruecolor($width, $high);

        switch($style['type']){
          case 'fixed':
            list($r, $g, $b) = $style['color'];
            $bgc = imagecolorallocate($im, $r, $g, $b);
            imagefill($im, 0, 0, $bgc);
            break;
          case 'gradient':
            if($style['cate'] == 'vertical'){
                $this->_verticalGradient($im, $high, $style['start_color'], $style['end_color']);
            }
            else if($style['cate'] == 'diagonal'){
                $this->_diagonalGradient($im, $width, $style['start_color'], $style['end_color']);
            }
            break;
          default:
            E('_genBgImage: 无效style');
            break;
        }

        return $im;
    }

    private function _verticalGradient($im, $high, $start_color, $end_color){
      list($start_r, $start_g, $start_b) = $start_color;
      list($end_r, $end_g, $end_b) = $end_color;

      $ch_r = ($start_r - $end_r) / $high;
      $ch_g = ($start_g - $end_g) / $high;
      $ch_b = ($start_b - $end_b) / $high;

      $tm_r = $end_r;
      $tm_g = $end_g;
      $tm_b = $end_b;
      for ($i = $high; $i >= 0; $i--) {
          $color = imagecolorallocate($im, $tm_r, $tm_g, $tm_b);
          imagefilledrectangle($im, 0, $i, $high, 1, $color);
          $tm_r += $ch_r;
          $tm_g += $ch_g;
          $tm_b += $ch_b;
      }
    }

    private function _diagonalGradient($im, $width, $start_color, $end_color){
      list($start_r, $start_g, $start_b) = $start_color;
      list($end_r, $end_g, $end_b) = $end_color;



      $ch_r = ($end_r - $start_r) / $width;
      $ch_g = ($end_g - $start_g) / $width;
      $ch_b = ($end_b - $start_b) / $width;

      $tm_r = $start_r;
      $tm_g = $start_g;
      $tm_b = $start_b;


      for ($i = 0; $i <= $width; $i++) {
          $color = imagecolorallocate($im, $tm_r, $tm_g, $tm_b);
          imageline($im, $i, 0, 0, $i, $color);
          imageline($im, $width - $i, $width, $width, $width - $i, $color);
          $tm_r += $ch_r;
          $tm_g += $ch_g;
          $tm_b += $ch_b;
      }
    }


    private function _setQrCodeTransparent($im){
        $search_index = imagecolorexact ($im, 0, 0, 0);
        imagecolortransparent($im, $search_index);

        return $im;
    }

    // private function _renderToCircle($im){
    //     $w = $this->_img_attr['center_img']['width'] + $this->_img_attr['center_img']['border'] * 2;
    //     $h = $this->_img_attr['center_img']['height'] + $this->_img_attr['center_img']['border'] * 2;
    //     $t_im = imagecreatetruecolor($w, $h);
    //     imagesavealpha($t_im, true);
    //     $bg = imagecolorallocatealpha($t_im, 255, 255, 255, 127);
    //     imagefill($t_im, 0, 0, $bg);
    //     if($this->_img_attr['center_img']['border']){
    //         $t_im = $this->_drawCircle($t_im, $this->_img_attr['center_img']['border_color']);
    //     }
    //     $t_im = $this->_drawCircle($t_im, $im);
    //     imagedestroy($im);
    //     return $t_im;
    // }



    //$c_x 圆心x坐标  $c_y 圆心y坐标  $r 圆半径
    // private function _drawCircle($dest_im, $bg_or_srcim){
    //     $width = imagesx($dest_im);
    //     $high = imagesy($dest_im);
    //
    //     if($width != $high){
    //         E('_drawCircle : 只能处理正方形');
    //     }
    //
    //     $y_x = $width / 2 ; //圆心X坐标
    //   	$y_y = $width / 2; //圆心Y坐标
    //
    //     //用固定颜色画圆
    //     if(is_array($bg_or_srcim) && count($bg_or_srcim) == 3){
    //         list($r, $g, $b) = $bg_or_srcim;
    //
    //         // 选择椭圆的颜色
    //         $col_ellipse = imagecolorallocate($dest_im, $r, $g, $b);
    //
    //         // 画一个椭圆
    //         imagefilledellipse($dest_im, $y_x, $y_y, $width, $width, $col_ellipse);
    //
    //     }
    //     //用原图裁圆填充图片
    //     else{
    //
    //         $src_width = imagesx($bg_or_srcim);
    //         //半径差
    //         $dis_r = ($width - $src_width) / 2;
    //         if($dis_r < 0 ){
    //             E('_drawCircle : 半径差不能为负数');
    //         }
    //
    //         $src_r   = $src_width / 2; //圆半径
    //       	$src_c_x = $src_r; //圆心X坐标
    //       	$src_c_y = $src_r; //圆心Y坐标
    //       	for ($x = 0; $x < $src_width; $x++) {
    //       		for ($y = 0; $y < $src_width; $y++) {
    //       			$rgbColor = imagecolorat($bg_or_srcim, $x, $y);
    //       			if (((($x - $src_r) * ($x - $src_r) + ($y - $src_r) * ($y - $src_r)) < ($src_r * $src_r))) {
    //       				imagesetpixel($dest_im, $x + $dis_r, $y + $dis_r, $rgbColor);
    //       			}
    //       		}
    //       	}
    //     }
    //     return $dest_im;
    // }
}
