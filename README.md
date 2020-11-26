# hyperf-tool

## 功能
* 该组件主要是根据开发扩展一些hyperf的工具，主要有gii工具、swagger的扩展等... 持续跟进中。。。
### 下载安装包
```
composer require eric-strive/hyperf-tool
```
### 同步配置
```bash
php bin/hyperf.php vendor:publish eric-strive/amqp-retry
```
* swagger模板配置在`App\Constants\SwaggerTemplate`，可以将重复出现的swagger配置在`SwaggerTemplate`中，
可以极大的简化controller swagger代码量

### swagger使用示例 这里需要配合 [daodao97/apidog](https://github.com/daodao97/apidog) 使用，感谢 `hyperf`开发中之一刀哥提供如此好用的`swagger`
* ValidationRequest ：template：是配置的模板，这可以将很复杂的注解简化
```bash
@ValidationRequest(template="authorization") 
```
* BodyValidation
简化body,这里将结合Request，可以将校验和Request结合，可以复用配置校验
```bash
@BodyValidation(validate="DictDetailRequest",scene="search",template="page_search")  
```
* SwaggerResponse
这里是扩展了ApiResponse,可以极大的依赖Request，达到复用以及简化controller代码
```bash
@SwaggerResponse(responseClass="DictDetailRequest",scene="response",template="page")
```
### 代码生成
* controller自动生成
```bash
php bin/hyperf.php make:controller TestUserController TestModel 测试我的功能
```
* request自动生成
```bash
php bin/hyperf.php make:request RoleRequest roles
```
#### 有任何问题请联系 `eric-strive@qq.com`


