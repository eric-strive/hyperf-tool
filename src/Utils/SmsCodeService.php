<?php
declare(strict_types=1);

namespace Hyperf\EricTool\Utils;

use Hyperf\EricTool\Exception\ToolException;

/**
 * 短信验证码,可以验证短信验证码发送间隔时间、ip次数限制（有开关）、每天次数限制、验证码长度
 * User: wangwei
 * Date: 2020/12/7
 * Time: 上午10:02
 */
class SmsCodeService
{
    private $mobile;
    private $cachePrefix;

    private $sendSumTimesKey;
    private $lastSendCodeKey;
    //各种数据key
    private const SEND_SUM_TIMES      = 'sum_times';
    private const SEND_IP_SUM_TIMES   = 'ip_sum_times';
    private const IS_IP_LIMIT         = 'is_ip_limit';
    private const CODE_LENGTH         = 'code_length';
    private const EVERY_TIME_INTERVAL = 'every_time_interval';
    private const LAST_SEND_CODE      = 'last_code';
    private const LIMIT_SEND_CODE     = 'limit_code';
    private const CODE_CACHE_TIME     = 'code_cache_time';
    public const  DAY_TIME            = 86400;

    public function __construct($mobile, $cachePrefix)
    {
        $this->mobile           = $mobile;
        $this->cachePrefix      = $cachePrefix;
        $this->sendSumTimesKey  = $this->getCacheKey($mobile,
            sprintf(sprintf('%s:%s', $cachePrefix, self::SEND_SUM_TIMES)));
        $this->lastSendCodeKey  = $this->getCacheKey($mobile,
            sprintf(sprintf('%s:%s', $cachePrefix, self::LAST_SEND_CODE)));
        $this->limitSendCodeKey = $this->getCacheKey($mobile,
            sprintf(sprintf('%s:%s', $cachePrefix, self::LIMIT_SEND_CODE)));
    }

    /**
     * 每个手机号每种类型每天发送短息的缓存key
     *
     * @param $mobile
     * @param $cachePrefix
     *
     * @return string
     */
    private function getCacheKey($mobile, $cachePrefix): string
    {
        return sprintf('%s:%s:%s', $cachePrefix, $mobile, date("Ymd"));
    }

    /**
     * 验证code是否有误
     *
     * @param $code
     */
    public function checkCode($code): void
    {
        $lastCode = redis()->get($this->lastSendCodeKey);
        if (empty($lastCode) || $lastCode !== $code) {
            $this->exceptionCast('验证码有误');
        }
    }

    /**
     * 获取以及验证code
     *
     * @return string
     */
    public function getCode(): string
    {
//        $this->checkLastSendCode();
        $this->checkSendSumTimes();
        if ($this->getConfig(self::IS_IP_LIMIT)) {
            $this->checkIpTimes();
        }

        return $this->createCode();
    }

    /**
     * 验证码失效
     */
    public function delCode(): void
    {
        redis()->del($this->lastSendCodeKey);
    }

    /**
     * 将code缓存
     *
     * @param $code
     */
    public function codeCache($code): void
    {
        redis()->set($this->limitSendCodeKey, $code, $this->getConfig(self::EVERY_TIME_INTERVAL));
        redis()->set($this->lastSendCodeKey, $code, $this->getConfig(self::CODE_CACHE_TIME));
        if ($this->getConfig(self::IS_IP_LIMIT)) {
            $ipCacheKey = $this->getCacheKey($this->mobile, $times = $this->checkIpTimes());
            sprintf(sprintf('%s:%s', $this->cachePrefix, getClientIp()));
            redis()->set($ipCacheKey, $times, self::DAY_TIME);
        }
        $times = $this->checkIpTimes();
        redis()->set($this->sendSumTimesKey, $times + 1, self::DAY_TIME);
    }

    private function createCode(): string
    {
        $codeLength = $this->getConfig(self::CODE_LENGTH);
        $code       = '';
        $pattern    = '1234567890';
        for ($i = 0; $i < $codeLength; $i++) {
            $code .= $pattern[random_int(0, 9)];
        }

        return $code;
    }

    //最后的code缓存【every_time_interval】秒
    private function checkLastSendCode(): void
    {
        $lastCode = redis()->get($this->limitSendCodeKey);
        if ($lastCode) {
            $this->exceptionCast('60秒后才能重新发送短信验证码!');
        }
    }

    //IP次数校验
    private function checkIpTimes()
    {
        $cacheKey = $this->getCacheKey($this->mobile, sprintf(sprintf('%s:%s', $this->cachePrefix, getClientIp())));
        $times    = redis()->get($cacheKey);
        redis()->del($cacheKey);
        $times = $times ?? 0;
        if ($times >= $this->getConfig(self::SEND_IP_SUM_TIMES)) {
            $this->exceptionCast('您获取短信验证过于频繁，请稍后再试！!');
        }

        return $times;
    }

    //检测单天发送次数
    private function checkSendSumTimes(): void
    {
        $times = redis()->get($this->sendSumTimesKey);
        $times = $times ?? 0;
        if ($times >= $this->getConfig(self::SEND_SUM_TIMES)) {
            $this->exceptionCast('您获取短信验证过于频繁，请稍后再试!');
        }
    }


    private function exceptionCast($message): void
    {
        throw new ToolException($message);
    }

    //获取配置，TODO 后期需要依赖config
    private function getConfig($key)
    {
        $configs = config('app_tool');

        return $configs[$key];
    }
}