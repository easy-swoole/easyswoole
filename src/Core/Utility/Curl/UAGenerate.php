<?php
// +----------------------------------------------------------------------
// | Chadanla [ Easy Multi Express Tracking ]
// +----------------------------------------------------------------------
// | Power BY Guangxi DuoMeile Trading Co., Ltd.
// +----------------------------------------------------------------------
// | Author: eValor <mipone@foxmail.com>
// +----------------------------------------------------------------------

namespace Core\Utility\Curl;

/**
 * 爬虫UA随机生成器
 * Class UAGenerate
 * @author : evalor <master@evalor.cn>
 * @package Core\Utility\Curl
 */
class UAGenerate
{
    // 操作系统类型
    const SYS_WIN     = 'WIN';
    const SYS_OSX     = 'OSX';
    const SYS_IOS     = 'IOS';
    const SYS_LINUX   = 'LINUX';
    const SYS_ANDROID = 'ANDROID';

    // 操作系统版本
    const SYS_BIT_X86 = 'X86';
    const SYS_BIT_X64 = 'X64';

    // UA 基本结构: Mozilla/5.0 (平台) 引擎版本 浏览器版本号

    /**
     * 生成随机UA
     * @author : evalor <master@evalor.cn>
     * @param bool $isWechat 是否微信UA
     * @param string $system 操作系统
     * @param string $bits 操作系统位数
     * @return string 随机UA字符串
     */
    public static function mock($system = 'UNKNOW', $isWechat = false, $bits = 'UNKNOW')
    {
        return 'Mozilla/5.0' . self::mockPlatform($system, $bits) . self::mockBrowser($isWechat);
    }

    /**
     * 生成操作系统以及版本号
     * @author : evalor <master@evalor.cn>
     * @param string $system 平台类型
     * @param string $bits 系统版本(手机不分版本)
     * @return string UA平台子串
     */
    private static function mockPlatform($system, $bits)
    {

        $Platform = [
            UAGenerate::SYS_WIN,
            UAGenerate::SYS_OSX,
            UAGenerate::SYS_IOS,
            UAGenerate::SYS_LINUX,
            UAGenerate::SYS_ANDROID,
        ];

        $Bit = [UAGenerate::SYS_BIT_X64, UAGenerate::SYS_BIT_X86,];

        if (!in_array($system, $Platform)) $system = $Platform[array_rand($Platform, 1)];
        if (!in_array($bits, $Bit)) $bits = $Bit[array_rand($Bit, 1)];


        $Platform = [
            UAGenerate::SYS_WIN     => [
                'version' => [
                    ' (Windows NT 5.0; ',  // Windows 2000
                    ' (Windows NT 5.1; ',  // Windows XP
                    ' (Windows NT 6.0; ',  // Windows Vista
                    ' (Windows NT 6.1; ',  // Windows 7
                    ' (Windows NT 6.2; ',  // Windows 8
                    ' (Windows NT 6.3; ',  // Windows 8.1
                    ' (Windows NT 10.0',   // Windows 10
                ],
                'bits'    => [
                    'X86' => 'WOW64 )',  // 32Bits
                    'X64' => 'Win64; x64)'   // 64Bits
                ]
            ],
            UAGenerate::SYS_LINUX   => [
                'version' => [
                    ' (X11; Linux; ',  // Linux UNKNOW
                    ' (X11; Ubuntu; ', // Linux Ubuntu
                    ' (X11; Fedora; ', // Linux Fedora
                    ' (X11; Centos; ', // Linux Centos
                ],
                'bits'    => [
                    'X86' => 'i686)',    // 32Bits
                    'X64' => 'x86_64)'   // 64Bits
                ]
            ],
            UAGenerate::SYS_OSX     => [
                'version' => [
                    ' 10_8_0)',   // Mountain Lion 10.8.0
                    ' 10_8_1)',   // Mountain Lion 10.8.1
                    ' 10_8_2)',   // Mountain Lion 10.8.2
                    ' 10_8_3)',   // Mountain Lion 10.8.3
                    ' 10_9_0)',   // Mavericks 10.9.0
                    ' 10_9_1)',   // Mavericks 10.9.1
                    ' 10_9_2)',   // Mavericks 10.9.2
                    ' 10_9_3)',   // Mavericks 10.9.3
                    ' 10_9_4)',   // Mavericks 10.9.4
                    ' 10_10_0)',  // Yosemite 10.10.0
                    ' 10_10_1)',  // Yosemite 10.10.1
                    ' 10_10_2)',  // Yosemite 10.10.2
                    ' 10_10_3)',  // Yosemite 10.10.3
                    ' 10_10_4)',  // Yosemite 10.10.4
                    ' 10_10_5)',  // Yosemite 10.10.5
                    ' 10_11_0)',  // EI Capitan 10.11.0
                    ' 10_11_1)',  // EI Capitan 10.11.1
                    ' 10_11_2)',  // EI Capitan 10.11.2
                    ' 10_11_3)',  // EI Capitan 10.11.3
                    ' 10_11_4)',  // EI Capitan 10.11.4
                    ' 10_11_5)',  // EI Capitan 10.11.5
                    ' 10_11_6)',  // EI Capitan 10.11.6
                    ' 10_12_0)',  // Sierra 10.12.0
                    ' 10_12_1)',  // Sierra 10.12.1
                    ' 10_12_2)',  // Sierra 10.12.2
                    ' 10_12_3)',  // Sierra 10.12.3
                    ' 10_12_4)',  // Sierra 10.12.4
                ],
                'bits'    => [
                    'X86' => ' (Macintosh; Intel Mac OS X',    // 32Bits
                    'X64' => ' (Macintosh; Intel x86_64 Mac OS X'   // 64Bits
                ],
            ],
            UAGenerate::SYS_ANDROID => [
                'version' => [
                    ' (Linux; Android 4.1.1; Mobile)', // Jelly Bean 4.1
                    ' (Linux; Android 4.1.2; Mobile)', // Jelly Bean 4.1
                    ' (Linux; Android 4.2; Mobile)',   // Jelly Bean 4.2
                    ' (Linux; Android 4.2; Mobile)',   // Jelly Bean 4.2
                    ' (Linux; Android 4.2.1; Mobile)', // Jelly Bean 4.2
                    ' (Linux; Android 4.2.2; Mobile)', // Jelly Bean 4.2
                    ' (Linux; Android 4.3; Mobile)',   // Jelly Bean 4.3
                    ' (Linux; Android 4.3.1; Mobile)', // Jelly Bean 4.3
                    ' (Linux; Android 4.4; Mobile)',   // KitKat 4.4
                    ' (Linux; Android 4.4.1; Mobile)', // KitKat 4.4
                    ' (Linux; Android 4.4.2; Mobile)', // KitKat 4.4
                    ' (Linux; Android 4.4.3; Mobile)', // KitKat 4.4
                    ' (Linux; Android 4.4.4; Mobile)', // KitKat 4.4
                    ' (Linux; Android 5.0; Mobile)',   // Lollipop 5.0
                    ' (Linux; Android 6.0; Mobile)',   // Marshmallow 6.0
                    ' (Linux; Android 7.0; Mobile)',   // AndroidN 7.0
                ]
            ],
            UAGenerate::SYS_IOS     => [
                'version' => [
                    ' (iPhone; CPU iPhone OS 5_0 like Mac OS X)',   // iPhone iOS 5.0
                    ' (iPhone; CPU iPhone OS 5_1 like Mac OS X)',   // iPhone iOS 5.1
                    ' (iPhone; CPU iPhone OS 6_1 like Mac OS X)',   // iPhone iOS 6.1
                    ' (iPhone; CPU iPhone OS 7_0 like Mac OS X)',   // iPhone iOS 7.0
                    ' (iPhone; CPU iPhone OS 7_1 like Mac OS X)',   // iPhone iOS 7.1
                    ' (iPhone; CPU iPhone OS 8_0 like Mac OS X)',   // iPhone iOS 8.0
                    ' (iPhone; CPU iPhone OS 8_1 like Mac OS X)',   // iPhone iOS 8.1
                    ' (iPhone; CPU iPhone OS 8_2 like Mac OS X)',   // iPhone iOS 8.2
                    ' (iPhone; CPU iPhone OS 8_4 like Mac OS X)',   // iPhone iOS 8.4
                    ' (iPhone; CPU iPhone OS 9_0 like Mac OS X)',   // iPhone iOS 9.0
                    ' (iPhone; CPU iPhone OS 9_1 like Mac OS X)',   // iPhone iOS 9.1
                    ' (iPhone; CPU iPhone OS 9_2 like Mac OS X)',   // iPhone iOS 9.2
                    ' (iPhone; CPU iPhone OS 9_3 like Mac OS X)',   // iPhone iOS 9.3
                ]
            ]
        ];

        switch ($system) {
            case UAGenerate::SYS_WIN:
                $version = $Platform[UAGenerate::SYS_WIN]['version'];
                return $version[array_rand($version, 1)] . $Platform[UAGenerate::SYS_WIN]['bits'][$bits];
            case UAGenerate::SYS_LINUX:
                $version = $Platform[UAGenerate::SYS_LINUX]['version'];
                return $version[array_rand($version, 1)] . $Platform[UAGenerate::SYS_LINUX]['bits'][$bits];
            case UAGenerate::SYS_OSX:
                $version = $Platform[UAGenerate::SYS_OSX]['version'];
                return $Platform[UAGenerate::SYS_OSX]['bits'][$bits] . $version[array_rand($version, 1)];
            case UAGenerate::SYS_ANDROID:
                $version = $Platform[UAGenerate::SYS_ANDROID]['version'];
                return $version[array_rand($version, 1)];
            case UAGenerate::SYS_IOS:
                $version = $Platform[UAGenerate::SYS_IOS]['version'];
                return $version[array_rand($version, 1)];
            default:
                $version = $Platform[UAGenerate::SYS_WIN]['version'];
                return $version[array_rand($version, 1)] . $Platform[UAGenerate::SYS_WIN]['bits'][$bits];
        }
    }

    /**
     * 生成浏览器以及引擎版本号
     * @author : evalor <master@evalor.cn>
     * @param bool $isWechat 是否模拟微信浏览器
     * @return string 浏览器引擎以及版本号
     */
    private static function mockBrowser($isWechat = false)
    {
        // makeFireFox
        $ffVer = range(40, 57);
        $ffVer = array_map(function ($ver) {
            return ' Gecko/20100101 Firefox/' . $ver . '.0';
        }, $ffVer);

        // makeChrome
        $chromeVer = range(43, 62);
        $chromeVer = array_map(function ($ver) {
            return ' AppleWebKit/537.36 (KHTML, like Gecko) Chrome/' . $ver . '.0.' . rand(1000, 2500) . '.0 Safari/537.36';
        }, $chromeVer);

        // 合并所有版本
        $Browser = array_merge($ffVer, $chromeVer);

        if ($isWechat) {
            $v1 = rand(3, 5);
            $v2 = rand(1, 9);
            $v3 = rand(100, 280);
            return $chromeVer[array_rand($chromeVer, 1)] . ' Mobile MicroMessenger/' . $v1 . '.' . $v2 . '.' . $v3;
        } else {
            return $Browser[array_rand($Browser, 1)];
        }

    }
}