<?php

namespace common\components;

use Yii;

/**
 * Class StringHelper
 * @package common\components
 */
class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * Rewrite parent logic
     * @inheritdoc
     * @param string $string
     * @param int $count
     * @param string $suffix
     * @param bool $encoding
     * @return string
     */
    protected static function truncateHtml($string, $count, $suffix, $encoding = false)
    {
        $config = \HTMLPurifier_Config::create(null);
        $config->set('Cache.SerializerPath', \Yii::$app->getRuntimePath());
        $lexer = \HTMLPurifier_Lexer::create($config);
        $tokens = $lexer->tokenizeHTML($string, $config, new \HTMLPurifier_Context());
        $openTokens = [];
        $totalCount = 0;
        $truncated = [];
        foreach ($tokens as $token) {
            if ($token instanceof \HTMLPurifier_Token_Start) { //Tag begins
                if ($totalCount < $count) {
                    $openTokens[$token->name] = isset($openTokens[$token->name]) ? $openTokens[$token->name] + 1 : 1;
                    $truncated[] = $token;
                }
            } elseif ($token instanceof \HTMLPurifier_Token_Text && $totalCount <= $count) { //Text
                if (false === $encoding) {
                    preg_match('/^(\s*)/um', $token->data, $prefixSpace) ?: $prefixSpace = ['',''];
                    $token->data = $prefixSpace[1] . self::truncateWords(ltrim($token->data), $count - $totalCount, '');
                    $currentCount = self::countWords($token->data);
                } else {
                    $token->data = self::truncate($token->data, $count - $totalCount, '', $encoding, false);
                    $currentCount = mb_strlen($token->data, $encoding);
                }
                $totalCount += $currentCount;
                $truncated[] = $token;
            } elseif ($token instanceof \HTMLPurifier_Token_End) { //Tag ends
                if (!empty($openTokens[$token->name])) {
                    $openTokens[$token->name]--;
                    $truncated[] = $token;
                }
            } elseif ($token instanceof \HTMLPurifier_Token_Empty) { //Self contained tags, i.e. <img/> etc.
                $truncated[] = $token;
            }
            if (0 === $openTokens && $totalCount >= $count) {
                break;
            }
        }
        $context = new \HTMLPurifier_Context();
        $generator = new \HTMLPurifier_Generator($config, $context);
        return rtrim($generator->generateFromTokens($truncated)) . ($totalCount >= $count ? $suffix : '');
    }




    /**
     * Rewrite/fix rtrim logic
     * @param string $string
     * @param int $count
     * @param string $suffix
     * @param bool $encoding
     * @return string
     * @inheritdoc
     */
    public static function truncate($string, $length, $suffix = '...', $encoding = null, $asHtml = false, $rtrim = true)
    {
        if ($asHtml) {
            return static::truncateHtml($string, $length, $suffix, $encoding ?: Yii::$app->charset);
        }
        if (mb_strlen($string, $encoding ?: Yii::$app->charset) > $length) {
            return ($rtrim)
                ? ((mb_substr($string, 0, $length, $encoding ?: Yii::$app->charset)) . $suffix)
                : (rtrim(mb_substr($string, 0, $length, $encoding ?: Yii::$app->charset)) . $suffix);
        } else {
            return $string;
        }
    }

    public static function hideEmail($content)
    {
        $emailRegex = "/(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})/";
        return preg_replace($emailRegex, '***', $content);
    }

    public static function hidePhoneNumber($content)
    {
        $phoneNumberRegex = "/((?:\+|00)[17](?: |\-)?|(?:\+|00)[1-9]\d{0,2}(?: |\-)?|(?:\+|00)1\-\d{3}(?: |\-)?)?(0\d|\([0-9]{3}\)|[1-9]{0,3})(?:((?: |\-)[0-9]{2}){4}|((?:[0-9]{2}){4})|((?: |\-)[0-9]{3}(?: |\-)[0-9]{4})|([0-9]{7}))/";
        return preg_replace($phoneNumberRegex, '***', $content);
    }
}
