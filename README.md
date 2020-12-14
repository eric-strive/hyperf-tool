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

### 导入导出工具
* 导入
```
$excelImport = new Import($file, $startRow, $endVerifyLine);
        $excelImport->process(function ($saveData) {
            // 处理$saveData
        }, $format);
$file ：导入的文件对象，UploadedFile对象
$startRow：文件开始读取的行，如果需要过滤前面的数据可以使用
$endVerifyLine：验证的列，这里防止文件后面空行过多导致报错，验证的行为空时直接终止取数

$saveData：获取的数据
$format：数据对应格式，具体行对应的字段配置
eg:
[
    'email'      => 2,
    'phone'      => 1,
    'user_name'  => 0,
    'real_name'  => 3,
    'job_number' => 0,
]
这里是字段分别对应的列，逻辑会自动根据对应的列来处理数据
```
* 导出
```
Download::downloadCsv($fileName, $data, true),$format);
$fileName:导出文件名称
$data:导出数据，这里可以是数组和对象，对象需要时model的对象
$format：数据处理的格式
eg:
[
    [
        'label' => "真实姓名",
        'value' => 'real_name',
        'width' => 15,
    ],
    [
        'label' => "创建时间",
        'value' => 'created_at',
        'width' => 20,
    ],
    [
        'label' => "类型",
        'value' => static function ($model) use ($employeeType) {
            return getExistParam($employeeType, $model->type);
        },
        'width' => 5,
    ],
]
上面配置：
label：csv头信息
value：取的对象或数组的值，可以是函数
width：该列的宽度，根据实际调整
```
#### 有任何问题请联系 `eric-strive@qq.com`


