<?php

require(__DIR__ . "/../../vendor/autoload.php");
require(__DIR__ . "/ExampleConfig.php");

use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient(array(
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME,
));

foreach ($otsClient->listTable(array()) as $tableName) {
    $otsClient->deleteTable(array('table_name' => $tableName));
}

$request = array(
    'table_meta' => array(
        'table_name' => 'MyTable',       // 表名为 MyTable
        'primary_key_schema' => array(
            'PK0' => 'INTEGER',          // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
            'PK1' => 'STRING',           // 第二个主键列名称为PK1, 类型为STRING
        ),
    ),
    'reserved_throughput' => array(
        'capacity_unit' => array(
            'read' => 5,                 // 预留读写吞吐量设置为：5个读CU，和10个写CU
            'write' => 10,
        ),
    ),
);
$otsClient->createTable($request);
sleep(125);




// 请注意调用UpdateTable有2分钟一次的限制，具体情况请参考OTS官网文档

$request = array(
    'table_name' => 'MyTable',
    'reserved_throughput' => array(
        'capacity_unit' => array(
            'read' => 20,                 // 预留读写吞吐量设置为：20个读CU，和30个写CU
            'write' => 30,
        ),
    ),

);
$response = $otsClient->updateTable($request);
print json_encode($response);

/* 样例输出：

{
    "capacity_unit_details": {
        "capacity_unit": {
            "read": 20,
            "write": 30
        },
        "last_increase_time": 1442225001,
        "last_decrease_time": null,
        "number_of_decreases_today": 0
    }
}

*/

