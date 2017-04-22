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





$otsClient->deleteTable(array('table_name' => "MyTable"));
