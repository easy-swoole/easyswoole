<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/23
 * Time: 11:30
 */

namespace  App\Utility\VerifyCode;



class Verify
{
    public static function create($codeStr){
        $codeSet = '12346789ABCDEFGHJKLMNPQRTUVWXY';//字符容器
        $fontSize = 25;     // 验证码字体大小(px)
        $useCurve = true;   // 是否画混淆曲线
        $useNoise = true;   // 是否添加杂点
        $imageH = 0;        // 验证码图片宽
        $imageL = 0;        // 验证码图片长
        $length = 4;        // 验证码位数
        $bg = array(243, 251, 254);  // 背景
        $_image = null;     // 验证码图片实例
        $_color = null;     // 验证码字体颜色
        $imageL = $length * $fontSize * 1.5 + $fontSize*1.5;
        $imageH = $fontSize * 2;
        // 建立一幅图像
        $_image = imagecreate($imageL,$imageH);
        // 设置背景
        imagecolorallocate($_image,$bg[0], $bg[1], $bg[2]);
        // 验证码字体随机颜色
        $_color = imagecolorallocate($_image, mt_rand(1,120), mt_rand(1,120), mt_rand(1,120));
        // 验证码使用随机字体   暂时不做
        $ttf = __DIR__.'/'."font.ttf";
        /**
         * 画杂点
         * 往图片上写不同颜色的字母或数字
         */
        for($i = 0; $i < 10; $i++){
            //杂点颜色
            $noiseColor = imagecolorallocate(
                $_image,
                mt_rand(150,225),
                mt_rand(150,225),
                mt_rand(150,225)
            );
            for($j = 0; $j < 5; $j++){
                // 绘杂点
                imagestring(
                    $_image,
                    5,
                    mt_rand(-10, $imageL),
                    mt_rand(-10, $imageH),
                    $codeSet[mt_rand(0, 27)], // 杂点文本为随机的字母或数字
                    $noiseColor
                );
            }
        }
        //画出横穿干扰线
        $A = mt_rand(1, $imageH/2);                  // 振幅
        $b = mt_rand(-$imageH/4,$imageH/4);   // Y轴方向偏移量
        $f = mt_rand(-$imageH/4,$imageH/4);   // X轴方向偏移量
        $T = mt_rand($imageH*1.5, $imageL*2);  // 周期
        $w = (2* M_PI)/$T;
        $px1 = 0;  // 曲线横坐标起始位置
        $px2 = mt_rand($imageL/2,$imageL * 0.667);  // 曲线横坐标结束位置
        for ($px=$px1; $px<=$px2; $px=$px+ 0.9) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $imageH/2;  // y = Asin(ωx+φ) + b
                $i = (int) (($fontSize - 6)/4);
                while ($i > 0) {
                    imagesetpixel($_image, $px + $i, $py + $i, $_color);  // 这里画像素点比imagettftext和imagestring性能要好很多
                    $i--;
                }
            }
        }

        $A = mt_rand(1,$imageH/2);                  // 振幅
        $f = mt_rand(-$imageH/4, $imageH/4);   // X轴方向偏移量
        $T = mt_rand($imageH*1.5, $imageL*2);  // 周期
        $w = (2* M_PI)/$T;
        $b = $py - $A * sin($w*$px + $f) - $imageH/2;
        $px1 = $px2;
        $px2 = $imageL;
        for ($px=$px1; $px<=$px2; $px=$px+ 0.9) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $imageH/2;  // y = Asin(ωx+φ) + b
                $i = (int) (($fontSize - 8)/4);
                while ($i > 0) {
                    imagesetpixel($_image, $px + $i, $py + $i, $_color);  // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    $i--;
                }
            }
        }
        //将验证码写入
        // 绘验证码
        if(!empty($codeStr)){
            $codeNX = 0; // 验证码第N个字符的左边距
            for ($i = 0; $i<$length; $i++) {
                $codeNX += mt_rand($fontSize*1.2,$fontSize*1.6);
                // 写一个验证码字符
                imagettftext($_image,$fontSize, mt_rand(-40, 70), $codeNX, $fontSize*1.5, $_color, $ttf, $codeStr[$i]);
            }
        }
        ob_start();
        imagepng($_image);
        $image = ob_get_contents();
        ob_end_clean();
        imagedestroy($_image);
        return new Result($codeStr,$image);
    }
}