<?php

namespace WechatMiniProgramServerMessageBundle\LegacyApi;

class WXBizMsgCrypt
{
    private string $m_sToken;

    private string $m_sEncodingAesKey;

    private string $m_sReceiveId;

    /**
     * 构造函数
     *
     * @param $token          string 开发者设置的token
     * @param $encodingAesKey string 开发者设置的EncodingAESKey
     * @param $receiveId      string, 不同应用场景传不同的id
     */
    public function __construct(string $token, string $encodingAesKey, string $receiveId)
    {
        $this->m_sToken = $token;
        $this->m_sEncodingAesKey = $encodingAesKey;
        $this->m_sReceiveId = $receiveId;
    }

    /*
    *验证URL
    *@param sMsgSignature: 签名串，对应URL参数的msg_signature
    *@param sTimeStamp: 时间戳，对应URL参数的timestamp
    *@param sNonce: 随机串，对应URL参数的nonce
    *@param sEchoStr: 随机串，对应URL参数的echostr
    *@param sReplyEchoStr: 解密之后的echostr，当return返回0时有效
    *@return：成功0，失败返回对应的错误码
    */
    public function VerifyURL(string $sMsgSignature, string $sTimeStamp, string $sNonce, string $sEchoStr, string &$sReplyEchoStr): int
    {
        if (43 !== strlen($this->m_sEncodingAesKey)) {
            return ErrorCode::$IllegalAesKey;
        }

        $pc = new Prpcrypt($this->m_sEncodingAesKey);
        // verify msg_signature
        $sha1 = new SHA1();
        $array = $sha1->getSHA1($this->m_sToken, $sTimeStamp, $sNonce, $sEchoStr);
        $ret = $array[0];

        if (0 !== $ret) {
            return (int) $ret;
        }

        $signature = $array[1];
        if (!is_string($signature)) {
            return ErrorCode::$ComputeSignatureError;
        }
        if ($signature !== $sMsgSignature) {
            return (int) ErrorCode::$ValidateSignatureError;
        }

        $result = $pc->decrypt($sEchoStr, $this->m_sReceiveId);
        if (0 !== $result[0]) {
            return (int) $result[0];
        }
        $decryptedValue = $result[1];
        if (!is_string($decryptedValue)) {
            return ErrorCode::$DecryptAESError;
        }
        $sReplyEchoStr = $decryptedValue;

        return (int) ErrorCode::$OK;
    }

    /**
     * 将公众平台回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     *
     * @param string $sReplyMsg
     * @param string|null $sTimeStamp
     * @param string $sNonce
     * @param string $sEncryptMsg
     * @return int 成功0，失败返回对应的错误码
     */
    public function EncryptMsg(string $sReplyMsg, ?string $sTimeStamp, string $sNonce, string &$sEncryptMsg): int
    {
        $pc = new Prpcrypt($this->m_sEncodingAesKey);

        // 加密
        $array = $pc->encrypt($sReplyMsg, $this->m_sReceiveId);
        $ret = $array[0];
        if (0 !== $ret) {
            return (int) $ret;
        }

        if (null === $sTimeStamp) {
            $sTimeStamp = (string) time();
        }
        $encrypt = $array[1];
        if (!is_string($encrypt)) {
            return ErrorCode::$EncryptAESError;
        }

        // 生成安全签名
        $sha1 = new SHA1();
        $array = $sha1->getSHA1($this->m_sToken, $sTimeStamp, $sNonce, $encrypt);
        $ret = $array[0];
        if (0 !== $ret) {
            return (int) $ret;
        }
        $signature = $array[1];
        if (!is_string($signature)) {
            return ErrorCode::$ComputeSignatureError;
        }

        // 生成发送的xml
        $xmlparse = new XMLParse();
        $sEncryptMsg = $xmlparse->generate($encrypt, $signature, $sTimeStamp, $sNonce);

        return (int) ErrorCode::$OK;
    }

    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     *
     * @param string $sMsgSignature
     * @param string|null $sTimeStamp
     * @param string $sNonce
     * @param string $sPostData
     * @param string $sMsg
     * @return int 成功0，失败返回对应的错误码
     */
    public function DecryptMsg(string $sMsgSignature, ?string $sTimeStamp, string $sNonce, string $sPostData, string &$sMsg): int
    {
        if (43 !== strlen($this->m_sEncodingAesKey)) {
            return ErrorCode::$IllegalAesKey;
        }

        $pc = new Prpcrypt($this->m_sEncodingAesKey);

        $encrypt = $this->extractEncryptFromPostData($sPostData);
        if (is_int($encrypt)) {
            return $encrypt;
        }

        if (null === $sTimeStamp) {
            $sTimeStamp = (string) time();
        }

        $signatureVerifyResult = $this->verifySignature($sMsgSignature, $sTimeStamp, $sNonce, $encrypt);
        if (0 !== $signatureVerifyResult) {
            return $signatureVerifyResult;
        }

        $result = $pc->decrypt($encrypt, $this->m_sReceiveId);
        if (0 !== $result[0]) {
            return (int) $result[0];
        }
        $decryptedMsg = $result[1];
        if (!is_string($decryptedMsg)) {
            return ErrorCode::$DecryptAESError;
        }
        $sMsg = $decryptedMsg;

        return (int) ErrorCode::$OK;
    }

    private function extractEncryptFromPostData(string $sPostData): string|int
    {
        if ((bool) json_validate($sPostData)) {
            $decodedData = json_decode($sPostData, true);
            if (!is_array($decodedData) || !isset($decodedData['Encrypt'])) {
                return ErrorCode::$ParseXmlError;
            }

            return (string) $decodedData['Encrypt'];
        }

        $xmlparse = new XMLParse();
        $array = $xmlparse->extract($sPostData);
        $ret = $array[0];

        if (0 !== $ret) {
            return (int) $ret;
        }

        $encryptValue = $array[1];
        if (!is_string($encryptValue)) {
            return ErrorCode::$ParseXmlError;
        }

        return $encryptValue;
    }

    private function verifySignature(string $sMsgSignature, string $sTimeStamp, string $sNonce, string $encrypt): int
    {
        $sha1 = new SHA1();
        $array = $sha1->getSHA1($this->m_sToken, $sTimeStamp, $sNonce, $encrypt);
        $ret = $array[0];

        if (0 !== $ret) {
            return (int) $ret;
        }

        $signature = $array[1];
        if (!is_string($signature)) {
            return ErrorCode::$ComputeSignatureError;
        }
        if ($signature !== $sMsgSignature) {
            return (int) ErrorCode::$ValidateSignatureError;
        }

        return 0;
    }
}
