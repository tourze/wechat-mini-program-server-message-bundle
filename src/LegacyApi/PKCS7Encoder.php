<?php

namespace WechatMiniProgramServerMessageBundle\LegacyApi;

/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对需要加密的明文进行填充补位
     *
     * @param $text 需要进行填充补位操作的明文
     *
     * @return 补齐明文字符串
     */
    public function encode($text)
    {
        $block_size = PKCS7Encoder::$block_size;
        $text_length = mb_strlen($text);
        // 计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ($text_length % PKCS7Encoder::$block_size);
        if (0 == $amount_to_pad) {
            $amount_to_pad = PKCS7Encoder::$block_size;
        }
        // 获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = '';
        for ($index = 0; $index < $amount_to_pad; ++$index) {
            $tmp .= $pad_chr;
        }

        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     *
     * @param decrypted 解密后的明文
     *
     * @return 删除填充补位后的明文
     */
    public function decode($text)
    {
        $pad = ord(mb_substr($text, -1));
        if ($pad < 1 || $pad > PKCS7Encoder::$block_size) {
            $pad = 0;
        }

        return mb_substr($text, 0, mb_strlen($text) - $pad);
    }
}
