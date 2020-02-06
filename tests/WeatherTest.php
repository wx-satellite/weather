<?php


namespace Wxsatellite\Weather\Tests;



use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery\Matcher\AnyArgs;
use PHPUnit\Framework\TestCase;
use Wxsatellite\Weather\Exceptions\HttpException;
use Wxsatellite\Weather\Exceptions\InvalidArgumentException;
use Wxsatellite\Weather\Weather;

// 单元测试就是针对目标方法行为进行断言
// mockery参考：https://www.houdunren.com/edu/front/topic/27?sid=1&mid=2
class WeatherTest extends TestCase {

    /*** 异常测试通常建议单独新建测试方法来测试  **/

    // 检查type参数
    public function testGetWeatherWithInvalidType() {

        $w = new Weather("99a699ba2e14a50a67fe069f93e9fa13");

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage("Invalid type values(base/all): foo");

        $w->getWeather("绍兴","foo");

        $this->fail("Failed to assert getWeather throw exception with invalid argument.");

    }

    // 检查format参数
    public function testGetWeatherWithInvalidFormat() {

        $w = new Weather("99a699ba2e14a50a67fe069f93e9fa13");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid format values(json/xml): array");

        $w->getWeather("绍兴","base","array");

        $this->fail("Failed to assert getWeather throw exception with invalid argument.");

    }

    /*** 异常测试通常建议单独新建测试方法来测试  **/


    public function testGetWeather() {
        // json
        $response = new Response(200,[],'{"success":true}'); // 创建模拟接口响应值
        $client = \Mockery::mock(Client::class)->makePartial(); // // 创建模拟 http client。

        // 指定将会产生的行为（在后续的测试中将会按下面的参数来调用），定义模拟对象get方法的行为（参数和返回值）
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => '99a699ba2e14a50a67fe069f93e9fa13',
                'city' => '绍兴',
                'output' => 'json',
                'extensions' => 'base',
            ]
        ])->andReturn($response);

        // makePartial允许调用父类的方法（父类指的是真正的Weather对象，下面的$weather对象其实子类的实例）
        $weather = \Mockery::mock(Weather::class,["99a699ba2e14a50a67fe069f93e9fa13"])->makePartial();

        // 定义模拟对象getHttpClient的行为（主要是返回值）
        $weather->allows()->getHttpClient()->andReturn($client);
        $this->assertSame(["success"=>true],$weather->getWeather('绍兴'));

        // xml
        $response = new Response(200,[],"<hello>World</hello>");
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => '99a699ba2e14a50a67fe069f93e9fa13',
                'city' => '绍兴',
                'output' => 'xml',
                'extensions' => 'all',
            ]
        ])->andReturn($response);

        $weather = \Mockery::mock(Weather::class,["99a699ba2e14a50a67fe069f93e9fa13"])->makePartial();
        $weather->allows()->getHttpClient()->andReturn($client);
        // 传入的参数和上面指定的get行为参数要一致，否会出错：
        // Either the method was unexpected or its arguments matched no expected argument list for this method
        $this->assertSame('<hello>World</hello>', $weather->getWeather('绍兴', 'all', 'xml'));
    }

    // 测试运行时异常
    public function testGetWeatherWithRuntimeException() {
        $client = \Mockery::mock(Client::class);

        $client->allows()->get(new AnyArgs()) // 由于上面的用例已经验证过参数传递，所以这里就不关心参数了。
            ->andThrow(new \Exception("runtime exception")); // 当调用 get 方法时会抛出异常。
        $weather = \Mockery::mock(Weather::class,["99a699ba2e14a50a67fe069f93e9fa13"])->makePartial();
        $weather->allows()->getHttpClient()->andReturn($client);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage("runtime exception");

        $weather->getWeather("绍兴");
    }


//    public function tearDown(): void
//    {
//        parent::tearDown(); // TODO: Change the autogenerated stub
//        \Mockery::close();
//
//    }


    public function testGetHttpClient() {
        $weather = new Weather("99a699ba2e14a50a67fe069f93e9fa13");

        $this->assertInstanceOf(Client::class, $weather->getHttpClient());
    }


    public function testSetGuzzleOptions() {
        $weather = new Weather("99a699ba2e14a50a67fe069f93e9fa13");

        $this->assertNull($weather->getHttpClient()->getConfig("timeout"));

        $weather->setGuzzleOptions(["timeout"=>5000]);

        $this->assertSame(5000, $weather->getHttpClient()->getConfig("timeout"));
    }
}